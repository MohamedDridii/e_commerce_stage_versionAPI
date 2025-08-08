<?php
namespace App\Controller\Api\OwnerApi;

use App\Entity\Store;
use App\Services\StoreApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api',name:'api.')]
class StoreApiController extends AbstractController
{

    public function __construct(
        private StoreApiService $storeApiService
    )
    {
        
    }
    #[Route('/stores',name:'get.stores',methods:['GET'])]
    public function getStores()
    {
        try
        {
            $stores=$this->storeApiService->getStores();
            return new JsonResponse
            ([
                'success'=>true,
                'data'=>json_decode($stores),
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'success'=>false,
                'message'=>'Couldn\'t fetch stores',
                'error'=>$e->getMessage(),

            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/store/{id}',name:'get.store',methods:['GET'])]
    public function getStroeById(Store $store)
    {
        try
        {
            $store=$this->storeApiService->getStoreById($store);
            return new JsonResponse([
                'success'=>true,
                'data'=>json_decode($store)
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'sucess'=>false,
                'message'=>'Couldn\'t fetch store',
                'error'=>$e->getMessage(),
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/store/create',name:'create.store',methods:['POST'])]
    public function createStore(Request $request)
    {
        try
        {
            $JsonData=$request->getContent();
            $this->storeApiService->createStore($JsonData);
            return new JsonResponse([
                'sucess'=>true,
                'message'=>'Created store successfully',
            ],Response::HTTP_OK);

        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'success'=>false,
                'message'=>'Couldn\'t create store',
                'error'=>$e->getMessage(),
                'trace' => $e->getTraceAsString()
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/store/update/{id}',name:'update.store',methods:['PUT','PATCH'])]
    public function updateStore(Request $request,Store $store)
    {
        try
        {
            $JsonData=$request->getContent();
            $this->storeApiService->updateStore($store,$JsonData);
            return new JsonResponse([
                'sucess'=>true,
                'message'=>'Updated store successfully',
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'sucess'=>false,
                'message'=>'Couldn\'t update store',
                'error'=>$e->getMessage(),
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/store/delete/{id}',name:'delete.store',methods:['DELETE'])]
    public function deleteStore(Store $store)
    {
        try
        {
            $this->storeApiService->deleteStore($store);
            return new JsonResponse([
                'sucess'=>true,
                'message'=>'Deleted store successfully',
            ]);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'sucess'=>false,
                'message'=>'couldn\'t delete store',
                'error'=>$e->getMessage(),
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
}
    
