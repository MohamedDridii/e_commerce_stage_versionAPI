<?php

namespace App\Controller\Client;

use App\Entity\Client;
use App\Form\ClientRegistrationType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ClientRegistrationController extends AbstractController
{
    #[Route('/client/register', name: 'client.register')]
    public function index(Request $request,UserPasswordHasherInterface $hasher,Security $security,EntityManagerInterface $em): Response
    {
        $client=new Client();
        $form=$this->createForm(ClientRegistrationType::class,$client);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            /** @var string $password */
            $password=$form->get('password')->getData();
            $client->setPassword($hasher->hashPassword($client,$password));
            $em->persist($client);
            $em->flush();
            return $security->login($client, AppAuthenticator::class, 'main');

        }
        return $this->render('registration/clientregister.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
