<?php

namespace App\Controller\Client;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client/product', name: 'client.product.')]
final class ClientProductController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(ProductRepository  $productrepo,CategoryRepository $categoryrepo,Request$request): Response
    {
        //read the category ID frm query parametre(the suery parametre takes any parametre passed in the path section in the view and add it to the url ala aaks route parametre li enti deifnehom kima {id} lezm taamlehom manuellement ama bel $request->query twali tasnaalek query param yetaada fel url b ay esm aaditou enti fel view kima fel cas hedhi category,edheka alehch aadina getInt('category',0))
        $categoryId=$request->query->getInt('category', 0);        
       //find all categories 
        $categories=$categoryrepo->findAll();
        //check if the category id is provided the filter with category else render all products 
        if($categoryId){
            $products=$productrepo->findBy(['category'=>$categoryId]);
        }else{
            $products=$productrepo->findAll();
        }
        return $this->render('client_product/index.html.twig', [
            'products' => $products,
            'categories'=>$categories,
            'selected'=>$categoryId
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
