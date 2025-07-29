<?php

namespace App\Controller\Owner;

use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Store;
use App\Form\ProductType;
use App\Repository\OrderLineRepository;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/owner/product', name: 'owner.product.')]
final class ProductController extends AbstractController
{
    #[Route('/home',name:'home')]
    public function ProductList(ProductRepository $repo):Response
    {
        // Get grouped product data
    $groupedProducts = $repo->findGroupedProducts();
    
    // Get full product entities for the first occurrence of each product
    $productIds = array_column($groupedProducts, 'id');
    $products = $repo->findBy(['id' => $productIds]);
    
    // Combine the data for the view
    $productsWithData = [];
    foreach ($products as $product) {
        foreach ($groupedProducts as $grouped) {
            if ($grouped['id'] == $product->getId()) {
                $productsWithData[] = [
                    'product' => $product,
                    'totalStock' => $grouped['totalStock'],
                    'storeCount' => $grouped['storeCount']
                ];
                break;
            }
        }
    }
        return $this->render('product/index.html.twig',[
            'productsWithData'=>$productsWithData
        ]);
    }

    #[Route('/create', name: 'create')]
    public function CreateProdcut(Request $request,EntityManagerInterface $em,StoreRepository $storeRepo): Response
    {
        $product=new Product();//create the new product 
        $stores=$storeRepo->findAll();//fetch all the stores availble 
        $storesStockData=[];//initailse emtpy collection to use pass it to the form 
        foreach($stores as $store){//loop on every store 
            $stock=new Stock();//create a stock 
            $stock->setStore($store);//associate the store we have to the stock created 
            $storesStockData[]=$stock;//ad the stock to the collection that we're going to pass to the form 
        }
        $form= $this->createForm(ProductType::class, $product,[//create the form and pass the stock data containing stock instance that contains the store ps:we're going to handle the showing of what field exactly in the form  
            'stock_data'=>$storesStockData
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){
            $em->persist($product);
            $stockData=$form->get('stock_data')->getData();

            foreach($form->get('stock_data') as $index=>$stockForm){//this return a collection of the subform stock data that contains the store the quantity and the check box we loop over it the index contains the position of each sub form and stockform contains the catual data from the form 
                $selected=$stockForm->get('selected')->getData();//contains which the check box is selected or not 
                $quantity=$stockForm->get('quantity')->getData();
                $store=$stockData[$index]->getStore();//fetch the store from the stockform to associate it with the stock 
                if($selected&&$quantity>0){
                    $stock=new Stock();
                    $stock->setProduct($product);
                    $stock->setStore($store);
                    $stock->setQuantity($quantity);
                    $em->persist($stock);

                }
            }   
            $em->flush();
            $this->addFlash('sucess','Product added succesfully');

            return $this->redirectToRoute('owner.product.home');
        }
        return $this->render('product/create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit', name: 'edit')]
    public function Editproduct(
        Product $product,EntityManagerInterface $em,Request $request,StoreRepository $storeRepo,StockRepository $stockRepo): Response 
        {
        // 1. Get all stores
        $stores = $storeRepo->findAll();

        // 2. Prepare the stock data
        $stockData = [];
        foreach ($stores as $store) {
            $existingStock = $stockRepo->findOneBy([
                'product' => $product,
                'store' => $store,
            ]);

            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setStore($store);

            if ($existingStock) {
                $stock->setQuantity($existingStock->getQuantity());
            } else {
                $stock->setQuantity(0);
            }

            $stockData[] = $stock;
        }

        // 3. Create the form
        $form = $this->createForm(ProductType::class, $product, [
            'stock_data' => $stockData
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedStockData = $form->get('stock_data');

            foreach ($submittedStockData as $stockForm) {
                $selected = $stockForm->get('selected')->getData();
                $quantity = $stockForm->get('quantity')->getData();
                /** @var Stock $stockEntity */
                $stockEntity = $stockForm->getData(); // already has product & store

                if ($selected && $quantity > 0) {
                    $stockEntity->setQuantity($quantity);

                    // Check if a stock entry already exists
                    $existingStock = $stockRepo->findOneBy([
                        'product' => $product,
                        'store' => $stockEntity->getStore(),
                    ]);

                    if ($existingStock) {
                        $existingStock->setQuantity($quantity);
                    } else {
                        $em->persist($stockEntity);
                    }
                }
            }

            $em->flush();
            $this->addFlash('success', 'Product modified successfully');
            return $this->redirectToRoute('owner.product.home');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete',name:'delete')]
    public function Deleteproduct(Product $product,EntityManagerInterface $em,StockRepository $stockRepo,OrderLineRepository $orderline):Response
    {
        // 1. any stock anywhere?
        $totalStock = $stockRepo->findRemainigStock($product);
        if ($totalStock > 0) {
            $this->addFlash('danger', 'Cannot delete: product still has stock.');
            return $this->redirectToRoute('owner.product.home');
        }
        // 2. any order lines?
        $orderCount = $orderline->VerifyOrderline($product);

        if ($orderCount > 0) {
            $this->addFlash('danger', 'Cannot delete: product is linked to orders.');
            return $this->redirectToRoute('owner.product.home');
        }
            $em->remove($product);
            $em->flush();
            $this->addFlash('success','product deleted ');
            return $this->redirectToRoute('owner.product.home');
    }

    #[Route('/{id}', name: 'show',)]
    public function show(ProductRepository $productRepo, $id,StockRepository $stockRepo): Response
    {
        $product=$productRepo->find($id);
        $stores=$stockRepo->findStoresByProductIdDQL($id);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'stores'=>$stores
        ]);
    }

}
