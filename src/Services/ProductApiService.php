<?php

namespace App\Services;

use App\Entity\Product;
use App\Entity\Stock;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use App\Repository\StoreRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductApiService
{
    public function __construct(
        private ProductRepository $productRepository,
        private StockRepository $stockRepository,
        private StoreRepository $storeRepository,
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    /**
     * Get all products with their total stock and store count
     */
    public function getAllProducts(): array
    {
        // Get all products with their relationships
        $products = $this->productRepository->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.stocks', 's')
            ->addSelect('c')
            ->addSelect('s')
            ->getQuery()
            ->getResult();
        
        $productsWithData = [];
        
        foreach ($products as $product) {
            // Calculate total stock and store count for this product
            $totalStock = 0;
            $storeIds = [];
            
            foreach ($product->getStocks() as $stock) {
                $totalStock += $stock->getQuantity();
                $storeIds[] = $stock->getStore()->getId();
            }
            
            $storeCount = count(array_unique($storeIds));
            
            // Serialize the product
            $productData = $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
            $productData = json_decode($productData, true);
            
            // Add calculated fields
            $productData['totalStock'] = $totalStock;
            $productData['storeCount'] = $storeCount;
            
            $productsWithData[] = $productData;
        }
        
        return $productsWithData;
    }

    /**
     * Get a single product with detailed stock information
     */
    public function getProductById(int $id): ?array
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return null;
        }

        // Get stores with stock for this product
        $stores = $this->stockRepository->findStoresByProductIdDQL($id);
        
        $data = $this->serializer->serialize($product, 'json', ['groups' => 'product:detail']);
        $data = json_decode($data, true);
        $data['stores'] = $stores;
        $data['totalStock'] = $product->getTotalStock();
        
        return $data;
    }

    /**
     * Create a new product with stock assignments
     */
    public function createProduct(array $productData): Product
    {
        $this->em->beginTransaction();
        
        try {
            // Create the product entity
            $product = new Product();
            $product->setName($productData['name']);
            $product->setPrice($productData['price']);
            $product->setImage($productData['image']);
            $product->setDescription($productData['description']);
            
            // Set category
            if (isset($productData['category_id'])) {
                $category = $this->categoryRepository->find($productData['category_id']);
                if (!$category) {
                    throw new \InvalidArgumentException('Category not found');
                }
                $product->setCategory($category);
            }

            // Validate product
            $this->validateProduct($product);

            $this->em->persist($product);
            $this->em->flush(); // Flush to get the product ID

            // Handle stock assignments
            if (isset($productData['stocks']) && is_array($productData['stocks'])) {
                $this->createStockEntries($product, $productData['stocks']);
            }

            $this->em->commit();
            
            return $product;

        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Update an existing product and its stock assignments
     */
    public function updateProduct(Product $product, array $productData): Product
    {
        $this->em->beginTransaction();
        
        try {
            // Update product basic fields
            if (isset($productData['name'])) {
                $product->setName($productData['name']);
            }
            if (isset($productData['price'])) {
                $product->setPrice($productData['price']);
            }
            if (isset($productData['image'])) {
                $product->setImage($productData['image']);
            }
            if (isset($productData['description'])) {
                $product->setDescription($productData['description']);
            }
            
            // Update category
            if (isset($productData['category_id'])) {
                $category = $this->categoryRepository->find($productData['category_id']);
                if (!$category) {
                    throw new \InvalidArgumentException('Category not found');
                }
                $product->setCategory($category);
            }

            // Validate product
            $this->validateProduct($product);

            // Handle stock updates
            if (isset($productData['stocks']) && is_array($productData['stocks'])) {
                $this->updateStockEntries($product, $productData['stocks']);
            }

            $this->em->flush();
            $this->em->commit();
            
            return $product;

        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Delete a product (with business rule checks)
     */
    public function deleteProduct(Product $product): void
    {
        // Check if product has remaining stock
        $totalStock = $this->stockRepository->findRemainingStock($product);
        if ($totalStock > 0) {
            throw new \InvalidArgumentException('Cannot delete: product still has stock');
        }

        // Check if product has order lines
        $orderCount = $this->em->getRepository(\App\Entity\OrderLine::class)
            ->createQueryBuilder('ol')
            ->select('COUNT(ol.id)')
            ->where('ol.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        if ($orderCount > 0) {
            throw new \InvalidArgumentException('Cannot delete: product is linked to orders');
        }

        $this->em->remove($product);
        $this->em->flush();
    }

    /**
     * Search products with filters
     */
    public function searchProducts(?int $categoryId, ?int $storeId, ?int $minStock, ?string $searchQuery): array
    {
        $products = $this->productRepository->findFilteredProducts($categoryId, $storeId, $minStock, $searchQuery);
        $serialized = $this->serializer->serialize($products, 'json', ['groups' => 'product:read']);
        return json_decode($serialized, true);
    }

    /**
     * Create stock entries for a product
     */
    private function createStockEntries(Product $product, array $stocksData): void
    {
        foreach ($stocksData as $stockData) {
            if (!isset($stockData['store_id']) || !isset($stockData['quantity'])) {
                continue;
            }

            if ($stockData['quantity'] <= 0) {
                continue;
            }

            $store = $this->storeRepository->find($stockData['store_id']);
            if (!$store) {
                throw new \InvalidArgumentException("Store with ID {$stockData['store_id']} not found");
            }

            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setStore($store);
            $stock->setQuantity((int) $stockData['quantity']);

            $this->em->persist($stock);
        }
        
        // Flush to save all stock entries
        $this->em->flush();
    }

    /**
     * Update stock entries for a product
     */
    private function updateStockEntries(Product $product, array $stocksData): void
    {
        foreach ($stocksData as $stockData) {
            if (!isset($stockData['store_id']) || !isset($stockData['quantity'])) {
                continue;
            }

            $store = $this->storeRepository->find($stockData['store_id']);
            if (!$store) {
                throw new \InvalidArgumentException("Store with ID {$stockData['store_id']} not found");
            }

            // Find existing stock entry
            $existingStock = $this->stockRepository->findOneBy([
                'product' => $product,
                'store' => $store
            ]);

            $quantity = (int) $stockData['quantity'];

            if ($existingStock) {
                if ($quantity > 0) {
                    $existingStock->setQuantity($quantity);
                } else {
                    // Remove stock entry if quantity is 0
                    $this->em->remove($existingStock);
                }
            } else if ($quantity > 0) {
                // Create new stock entry
                $stock = new Stock();
                $stock->setProduct($product);
                $stock->setStore($store);
                $stock->setQuantity($quantity);
                $this->em->persist($stock);
            }
        }
    }

    /**
     * Serialize product for API response
     */
    public function serializeProduct(Product $product, array $groups = ['product:read']): array
    {
        $serialized = $this->serializer->serialize($product, 'json', ['groups' => $groups]);
        return json_decode($serialized, true);
    }

    /**
     * Validate product entity
     */
    private function validateProduct(Product $product): void
    {
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }
    }
}