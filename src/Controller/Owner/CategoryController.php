<?php

namespace App\Controller\Owner;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('owner/category', name: 'owner.category.')]

final class CategoryController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function CategoryList(CategoryRepository $repo): Response
    {
        $list=$repo->findAll();
        
        return $this->render('category/index.html.twig', [
            'list'=>$list
        ]);
    }
    #[Route('/create',name:'create')]
    public function CreateCategory(Request $request,EntityManagerInterface $em):Response
    {
        $category=new Category();
        $form= $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid()){
            $em->persist($category);
            $em->flush();
            $this->addFlash('success','category created successfuly');
            return $this->redirectToRoute('owner.category.home');

        }
        return $this->render('category/create.html.twig',[
            'form'=>$form
        ]);
    }
    #[Route('{id}/edit',name:'edit')]
    public function EditCategory (Category $category,EntityManagerInterface $em,Request $request):Response
    {
        $form=$this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){
            $em->flush();
            $this->addFlash('success','Category edited successfuly');
            return $this->redirectToRoute('owner.category.home');
        }
        
        
        return $this->render('category/edit.html.twig',[
            'form'=>$form
        ]);
    }
    #[Route('/{id}/delete',name:'delete')]
    public function DeleteCategory (Category $category,EntityManagerInterface $em):Response
    {
        $em->remove($category);
        $em->flush();
        $this->addFlash('success','category Deleted successfuly');
        return $this->redirectToRoute('owner.category.home');
    }

}

