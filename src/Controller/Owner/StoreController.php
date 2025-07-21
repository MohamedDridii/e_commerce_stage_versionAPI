<?php

namespace App\Controller\Owner;

use App\Entity\Store;
use App\Form\StoreType;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/owner/store', name: 'owner.store.')]
final class StoreController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function storeList(StoreRepository $storeRepo): Response
    {
        $list=$storeRepo->findAll();
        return $this->render('store/index.html.twig', [
            'list' => $list,
        ]);
    }
    #[Route('/create', name: 'create')]
    public function CreateStore(Request $request,EntityManagerInterface $em): Response
    {
        $store=new Store();
        $form=$this->createForm(StoreType::class,$store);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $em->persist($store);
            $em->flush();
            $this->addFlash('sucess','Store created succesfully');
            return $this->redirectToRoute('owner.store.home');
        }
        return $this->render('store/create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit', name: 'edit')]
    public function EditStore(Store $store,Request $request,EntityManagerInterface $em): Response
    {
        
        $form=$this->createForm(StoreType::class,$store);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $em->persist($store);
            $em->flush();
            $this->addFlash('sucess','Store modified succesfully');
            return $this->redirectToRoute('owner.store.home');
        }
        return $this->render('store/edit.html.twig', [
            'form' => $form,
            'store'=> $store
        ]);
    }
    #[Route('/{id}/delete',name:'delete')]
    public function StoreDelete(Store $store,EntityManagerInterface $em){
        $em->remove($store);
        $em->flush();
        $this->addFlash('success','Store deleted successfuly ');
        return $this->redirectToRoute('owner.store.home');
    }

}
