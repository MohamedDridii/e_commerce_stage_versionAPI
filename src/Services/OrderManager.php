<?php
namespace App\Services;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class OrderManager{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em=$em;
    }
    //association de produit a un client 
    public function createOrderForCLient(Client $client):Order{
        $order=new Order();
        $order->setClient($client);
        $order->setNumeroCommande($this->generateOrderNumber());
        $this->em->persist($order);
        $this->em->flush();
        return $order;
    }
    //ajout de produit dans le panier 
    public function addProductToBucket(Order $order,Product $product,int $quantity):OrderLine{
        foreach($order->getOrderLines()as $line){//parcours la liste de order line de produit 
            if($line->getProduct()->getId()===$product->getId()){//si le produit existe deja en incremente la quantite seulement 
                $line->setQuantity($line->getQuantity()+$quantity);
                $this->em->flush();
                return $line;
            }
        }
        //sinon on cree un nouveau orderline 
        $line=new OrderLine();
        $line->setOrderr($order);
        $line->setProduct($product);
        $line->setQuantity($quantity);
        //et on ajoute orderline a order
        $order->addOrderLine($line);
        $this->em->persist($order); 
        $this->em->flush(); 
        return $line;
    }
    private function generateOrderNumber():string{
        return 'CMD'. date('Ymd') . '-' .str_pad(random_int(1,9999),4,'0',STR_PAD_LEFT);
    }
}
