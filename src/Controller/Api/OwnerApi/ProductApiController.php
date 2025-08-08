<?php

namespace App\Controller\Api\OwnerApi;

use App\Entity\Product;
use App\Services\ProductApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products', name: 'api.product.')]
class ProductApiController extends AbstractController
{
    public function __construct(
        private ProductApiService $productService
    ) {}

    /**
     * Get all products with stock summary
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function getAllProducts(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts();

            return new JsonResponse([
                'success' => true,
                'data' => $products,
                'total' => count($products)
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Error retrieving products: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get a single product with detailed stock information
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getProduct(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            if (!$product) {
                return $this->createErrorResponse('Product not found', Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                'success' => true,
                'data' => $product
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Error retrieving product: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Create a new product with stock assignments
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createProduct(Request $request): JsonResponse
    {
        try {
            // Validate JSON input
            $jsonData = $request->getContent();
            if (empty($jsonData)) {
                return $this->createErrorResponse('Request body cannot be empty', Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->createErrorResponse('Invalid JSON format', Response::HTTP_BAD_REQUEST);
            }

            // Validate required fields
            $requiredFields = ['name', 'price', 'image', 'description', 'category_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->createErrorResponse("Field '{$field}' is required", Response::HTTP_BAD_REQUEST);
                }
            }

            // Create product through service
            $product = $this->productService->createProduct($data);

            // Serialize response
            $responseData = $this->productService->serializeProduct($product, ['product:detail']);

            return new JsonResponse([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $responseData
            ], Response::HTTP_CREATED);

        } catch (\InvalidArgumentException $e) {
            return $this->createErrorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Error creating product: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update an existing product and its stock assignments
     */
    #[Route('/update/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function updateProduct(Product $product, Request $request): JsonResponse
    {
        try {
            // Validate JSON input
            $jsonData = $request->getContent();
            if (empty($jsonData)) {
                return $this->createErrorResponse('Request body cannot be empty', Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->createErrorResponse('Invalid JSON format', Response::HTTP_BAD_REQUEST);
            }

            // Update product through service
            $updatedProduct = $this->productService->updateProduct($product, $data);

            // Serialize response
            $responseData = $this->productService->serializeProduct($updatedProduct, ['product:detail']);

            return new JsonResponse([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $responseData
            ], Response::HTTP_OK);

        } catch (\InvalidArgumentException $e) {
            return $this->createErrorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Error updating product: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete a product
     */
    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteProduct(Product $product): JsonResponse
    {
        try {
            $productName = $product->getName();

            // Delete product through service
            $this->productService->deleteProduct($product);

            return new JsonResponse([
                'success' => true,
                'message' => "Product '{$productName}' deleted successfully"
            ], Response::HTTP_OK);

        } catch (\InvalidArgumentException $e) {
            return $this->createErrorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Error deleting product: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Search products with filters
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function searchProducts(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->query->getInt('category_id', 0) ?: null;
            $storeId = $request->query->getInt('store_id', 0) ?: null;
            $minStock = $request->query->getInt('min_stock', 0) ?: null;
            $searchQuery = $request->query->get('q', '') ?: null;

            $products = $this->productService->searchProducts($categoryId, $storeId, $minStock, $searchQuery);

            return new JsonResponse([
                'success' => true,
                'data' => $products,
                'total' => count($products),
                'filters' => [
                    'category_id' => $categoryId,
                    'store_id' => $storeId,
                    'min_stock' => $minStock,
                    'search_query' => $searchQuery
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Error searching products: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Create standardized error response
     */
    private function createErrorResponse(string $message, int $statusCode): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
}