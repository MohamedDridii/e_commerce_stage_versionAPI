<?php

namespace App\Controller\Owner;

use App\Form\RegistrationFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/owner', name: 'owner.')]
final class OwnerController extends AbstractController
{
    #[IsGranted('ROLE_OWNER')]
    #[Route('/home', name: 'home')]
    public function index(ProductRepository $repo): Response
    {
        $products=$repo->findAll();
        $totStock=0;
        foreach($products as $product){
            $totStock+= $product->getTotalStock();
        }
        return $this->render('owner/owner.html.twig', [
            'totalProductsInStock'=>$totStock
        ]);
    }
    #[Route('/edit',name:'edit')]
    public function edit(Request $request,EntityManagerInterface $em)
    {
        $user=$this->getUser();
        $form=$this->createForm(RegistrationFormType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->flush();
            return $this->redirectToRoute('owner.home');
        }
        return $this->render('owner/edit.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
