<?php

namespace App\Controller\Owner;

use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Store;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
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
        $list=$repo->findAll();
        return $this->render('product/index.html.twig',[
            'list'=>$list
        ]);
    }

    #[Route('/create', name: 'create')]
    public function CreateProdcut(Request $request,EntityManagerInterface $em): Response
    {
        $product=new Product();
        $form= $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $em->persist($product);
            // now we create the stock for this product so we can relate the product and the store using the pivot table stock 
            $stock=new Stock();
            //we set the quantity of the product 
            $stock->setQuantity($form->get('quantity')->getData());
            //we set the product 
            $stock->setProduct($product);
            // and set the store related to the product 
            $stock->setStore($form->get('store')->getData());
            $em->persist($stock);
            $em->flush();
            $this->addFlash('sucess','Product added succesfully');

            return $this->redirectToRoute('owner.product.home');
        }
        return $this->render('product/create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit',name:'edit')]
    public function Editproduct(Product $product,EntityManagerInterface $em,Request $request,StockRepository $stock):Response
    {
        $existingStock=$stock->findOneBy(['product'=>$product]);
        // the params added are to fill the value of fields quantity adn store as they're not in the entity product so we have to add them manually 
        $form=$this->createForm(ProductType::class,$product,[
            'quantity'=>$existingStock ? $existingStock->getQuantity():null,
            'store'=>$existingStock ? $existingStock->getStore():null
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            //handle the stock type data
            $quantity=$form->get('quantity')->getData();
            $store=$form->get('store')->getData();

            // update the stock we didn't put any condition here because the quantity and the store field are required in the form  
            
            $existingStock->setQuantity($quantity);
            $existingStock->setStore($store);
            
            
            $em->flush();
            $this->addFlash('success','Product modified successfuly');
            return $this->redirectToRoute('owner.product.home');
        }
        return $this->render('product/edit.html.twig',[
            "form"=>$form
        ]);
    }
    #[Route('/{id}/delete',name:'delete')]
    public function Deleteproduct(Product $product,EntityManagerInterface $em,Request $request):Response
    {
        $em->remove($product);
        $em->flush();
        $this->addFlash('success','product deleted ');
        return $this->redirectToRoute('owner.product.home');
    }

}
