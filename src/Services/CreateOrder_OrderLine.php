<?php
namespace App\Services;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;

class CreateOrder_OrderLine{
    public $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em=$em;
    }

    public function createOrder(Client $client):Order
    {
        $order=new Order();
        $order->setClient($client);
        $order->setNumeroCommande('CMD'.date('Ymd-His'));
        // Gestion des valeurs nulles pour l'adresse
        $address = $client->getAdress() ?? 'Address not specified';
        $order->setAdressLivraison($address);        $this->em->persist($order);
        return $order;
    }
    public function createOrderLine(Order $order,Product $product,$quantity)
    {
        $line=new OrderLine();
        $line->setOrderr($order);
        $line->setProduct($product);
        $line->setQuantity($quantity);
        $this->em->persist($line);
    }

 }