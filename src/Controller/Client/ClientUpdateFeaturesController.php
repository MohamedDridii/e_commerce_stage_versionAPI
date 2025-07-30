<?php

namespace App\Controller\Client;

use App\Form\ClientRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client/Profile', name: 'client.profile.')]
final class ClientUpdateFeaturesController extends AbstractController
{
    #[Route('/edit',name:'edit')]
    public function edit(EntityManagerInterface $em,Request $request): Response
    {
        $user=$this->getUser();
        $form=$this->createForm(ClientRegistrationType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid())
        {
            $em->flush();
            $this->addFlash('success','Profile updated successfully');
            return $this->redirectToRoute('client.product.home');
        }
        return $this->
        render('client_update_features/index.html.twig',[
            'form'=>$form->createView(),
        ]);
    }
}
