<?php

namespace App\Controller\Owner;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/owner', name: 'owner.')]
final class OwnerController extends AbstractController
{
    #[IsGranted('ROLE_OWNER')]
    #[Route('/home', name: 'home')]
    public function index(UserPasswordHasherInterface $passwordhash,EntityManagerInterface $em): Response
    {

        
        return $this->render('owner/owner.html.twig', [
            'controller_name' => 'You are now in the owner home page!!',
        ]);
    }
}
