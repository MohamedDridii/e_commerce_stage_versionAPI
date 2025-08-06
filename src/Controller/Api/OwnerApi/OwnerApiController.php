<?php

namespace App\Controller\Api\OwnerApi;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/owner')]
class OwnerApiController extends AbstractController
{
    #[IsGranted('ROLE_OWNER')]
    #[Route('/dashboard', name: 'api_owner_dashboard', methods: ['GET'])]
    public function dashboard(ProductRepository $productRepo, OrderRepository $orderRepo): JsonResponse
    {
        // Calcul du stock total
        $products = $productRepo->findAll();
        $totalStock = 0;
        foreach ($products as $product) {
            $totalStock += $product->getTotalStock();
        }

        // Statistiques des commandes
        $totalOrdersToday = $orderRepo->countOrdersToday();
        $totalProfitToday = $orderRepo->countProfitToday();

        // Retour des données en JSON
        return $this->json([
            'status' => 'success',
            'data' => [
                'totalProductsInStock' => $totalStock,
                'totalOrdersToday' => $totalOrdersToday,
                'totalProfitToday' => $totalProfitToday
            ]
        ], Response::HTTP_OK);
    }
    #[IsGranted('ROLE_OWNER')]
    #[Route('/profile', name: 'api_owner_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'status' => 'success',
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                // Ajoutez ici d'autres champs que vous voulez exposer
            ]
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_OWNER')]
    #[Route('/edit', name: 'api_owner_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Get JSON data from request
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid JSON data'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Update user properties if provided
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            // Hash the password before saving
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        
        // Add other fields as needed based on your User entity
        // Example: if (isset($data['firstName'])) { $user->setFirstName($data['firstName']); }
        
        // Validate the user entity
        $errors = $validator->validate($user);
        
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            
            return $this->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $em->flush();
            
            return $this->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail()
                    // Add other safe fields to return
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to update profile'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}