<?php
namespace App\Services;

use App\Entity\Store;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class StoreApiService
{
    public function __construct(
        private StoreRepository $storeRepository,
        private SerializerInterface $serializer,
        private EntityManagerInterface $em

    )
    {}
    public function getStores()
    {
        $stores=$this->storeRepository->findAll();
        return $this->serializer->serialize(
        $stores,
        'json',
        ['groups'=>'store:read']);
    }
    public function getStoreById($id)
    {
        $store=$this->storeRepository->find($id);
        return $this->serializer->serialize(
        $store,
        'json',
        ['groups'=>'store:read']);
    }
    public function createStore($JsonData)
    {
        $store=$this->serializer->deserialize(
        $JsonData,
        Store::class,
        'json',
        ['groups'=>'store:read']);
        $this->em->persist($store);
        $this->em->flush();
    }
    public function updateStore(Store $store,$JsonData)
    {
        $store=$this->serializer->deserialize(
        $JsonData,
        Store::class,
        'json',
        ['object_to_populate'=>$store,'groups'=>'store:read']);
        $this->em->flush();
    }    
    public function deleteStore(Store $store)
    {
        $this->em->remove($store);
        $this->em->flush();
    }
}