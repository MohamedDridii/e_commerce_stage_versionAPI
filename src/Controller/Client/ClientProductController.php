<?php

namespace App\Controller\Client;

use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client/product', name: 'client.product.')]
final class ClientProductController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(ProductRepository  $productrepo): Response
    {
        $products=$productrepo->findAll();

        return $this->render('client_product/index.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/show/{id}', name: 'show')]
    public function show(ProductRepository  $productrepo,StockRepository $stockRepo,$id): Response
    {
        $products=$productrepo->find($id);
        $stores=$stockRepo->findStoresByProductIdDQL($id);

        return $this->render('client_product/showProduct.html.twig', [
            'product' => $products,
            'stores'=>$stores
        ]);
    }
}
