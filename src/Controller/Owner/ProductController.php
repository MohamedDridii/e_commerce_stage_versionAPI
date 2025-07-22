<?php

namespace App\Controller\Owner;

use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Store;
use App\Form\ProductType;
use App\Repository\ProductRepository;
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
            $stock=new Stock();
            $stock->setQuantity($form->get('quantity')->getData());
            $stock->setProduct($product);
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
    public function Editproduct(Product $product,EntityManagerInterface $em,Request $request):Response
    {
        $form=$this->createForm(ProductType::class,$product);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $em->flush();
            $this->addFlash('success','Product modified successfuly');
            return $this->redirectToRoute('owner.product.home');
        }
        return $this->render('product/edit.html.twig');
    }
    #[Route('/{id}/delete',name:'edit')]
    public function Deleteproduct(Product $product,EntityManagerInterface $em,Request $request):Response
    {
        $em->remove($product);
        $em->flush();
        $this->addFlash('success','product deleted ');
        return $this->redirectToRoute('owner.product.home');
    }

}
