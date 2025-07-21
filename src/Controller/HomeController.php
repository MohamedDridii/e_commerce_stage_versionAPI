<?php

namespace App\Controller;

use App\Entity\Owner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(UserPasswordHasherInterface $passwordhash,EntityManagerInterface $em): Response
    {

        /*$owner=new Owner();
        $owner->setEmail('mohamed@gmail.com');
        $owner->setPassword($passwordhash->hashPassword($owner,'1234'));
       // $em->persist($owner);
        //$em->flush();*/

        //dd($owner);
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
