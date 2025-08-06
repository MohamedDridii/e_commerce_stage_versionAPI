<?php
namespace App\Services;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\ValidationException;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryApiService
{
    

    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    public function getAllCategories()
    {
        $categories=$this->categoryRepository->findAll();
        return $this->serializer->serialize($categories, 'json', ['groups' => ['category:read']]);
    }

    public function getCategoriebyId(Category $category)
    {
        $categories=$this->categoryRepository->find($category);
        return $this->serializer->serialize($categories, 'json', ['groups' => ['category:read']]);
    }

    public function createCategory(string $JsonData)
    {
        $category=$this->serializer->deserialize($JsonData, Category::class, 'json', [
            'groups' => ['category:read']
        ]);
        $this->validateCategory($category);
        
        $this->em->persist($category);
        $this->em->flush();
    }

    public function updateCategory(Category $category,string $JsonData)
    {
        $category=$this->serializer->deserialize(
            $JsonData,
            Category::class,
            'json',
            ['object_to_populate'=>$category,'groups'=>'category:read']);

            $this->validateCategory($category);
            $this->em->persist($category);
            $this->em->flush();
    }   

    public function deleteCategory(Category $category)
    {
        $this->em->remove($category);
        $this->em->flush();
    }

    private function validateCategory(Category $category): void
    {
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }
}