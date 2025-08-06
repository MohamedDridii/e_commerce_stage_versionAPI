<?php

namespace App\Controller\Api\OwnerApi;

use App\Entity\Category;
use App\Services\CategoryApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;




class CategoryApiController extends AbstractController
{
   public function __construct(Private CategoryApiService $category_api)
   {
    
   }
    #[Route('/api/category', name: 'api.category.all',methods:['GET'])]
    public function getallCategories():JsonResponse
    {
        try
        {
            //this approach is a bit complicated because i can simply return this->json() but in this i can retunr extra field in the response like success but i have to do json_encode so the data isn't encoded twice in serilize and jsonResponse 
            $categories=$this->category_api->getAllCategories();
            return new JsonResponse([
                'success'=>true,
                'data'=>json_decode($categories,true) 
                
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving categories',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);        }
    }
    #[Route('/api/category/{id}', name: 'api.category.id',methods:['GET'])]
    public function getCategoryByid(Category $category)
    {
        try
        {
            $data=$this->category_api->getCategoriebyId($category);
            return new JsonResponse([
                'success'=>true,
                'data'=>json_decode($data,true)//essential because we already serizlized the data in getCategoribyId
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving category',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/category/create',name:'api.category.create',methods:['POST'])]
    public function createCategory(Request $request)
    {
        try
        {
            $JsonData=$request->getContent();
            $this->category_api->createCategory($JsonData);
            return new JsonResponse([
                'success'=>true,
                'message'=>'category created successfuly'
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error creating category',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/category_update/{id}',name:'api.category.update',methods:['PUT','PATCH'])]
    public function updateCategory(Request $request,Category $category)
    {
        try
        {
            $JsonData=$request->getContent();
            $category_name=$category->getName();
            $this->category_api->updateCategory($category,$JsonData);
            return new JsonResponse([
                'success'=>true,
                'message'=>"Category'{$category_name}' updated successfuly"
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error updating category',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/api/category_delete/{id}',name:'api.category.delete',methods:['DELETE'])]
    public function deleteCategory(Category $category)
    {
        try
        {
            $category_name=$category->getName();
            $this->category_api->deleteCategory($category);

            return new JsonResponse([
                'success'=>true,
                'message'=>"Category {$category_name} deleted successfuly"
            ],Response::HTTP_OK);
        }
        catch(\Exception $e)
        {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error deleting category',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
