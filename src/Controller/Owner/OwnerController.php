<?php

namespace App\Controller\Owner;

use App\Entity\Product;
use App\Repository\ProductRepository;
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
}
