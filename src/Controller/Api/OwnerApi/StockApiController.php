<?php
namespace App\Controller\Api\OwnerApi;

use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class StockApiController extends AbstractController
{
    #[Route('api/stock_details', name:'api.stock_details',methods:['GET'])]
    public function stockDetails(StockRepository $stockrepo,SerializerInterface $serializer)
    {
        try
        {
            $stocks=$stockrepo->findAllWithDetailsForApi();
            $stockdata=$serializer->serialize($stocks,'json');
            return new JsonResponse([
                'sucess'=>true,
                'data'=>json_decode($stockdata,true),

            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'sucess'=>false,
                'message'=>'error fetching stock details',
                'error'=>$e->getMessage()
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}