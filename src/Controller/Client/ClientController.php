<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    #[Route('/client/controllr', name: 'client.home')]
    public function index(): Response
    {
        return $this->render('client_controllr/index.html.twig', [
            'controller_name' => 'ClientControllrController',
        ]);
    }
}
