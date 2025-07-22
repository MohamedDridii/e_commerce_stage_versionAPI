<?php

namespace App\Controller\Owner;

use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('owner/stock', name: 'owner.stock.')]
final class StockController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function StockList(StockRepository $stock): Response
    {
        $stocks=$stock->findAllWithDetails();
        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
        ]);
    }
}
