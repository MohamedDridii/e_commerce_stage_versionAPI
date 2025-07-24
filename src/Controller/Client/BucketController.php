<?php

namespace App\Controller\Client;

use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Services\OrderManager;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Node\Expression\ReturnNumberInterface;

#[Route('client/bucket', name: 'client.bucket.')]
final class BucketController extends AbstractController
{
    public $client;
    public function __construct()
    {
        $this->client=$this->getUser();

    }
    #[Route('/home', name: 'home')]
    public function index(OrderRepository $order): Response
    {
        //on va afficher les produit existant dans le panier deja 
        //recupere le client 
        if(!$this->client){
            throw $this->createAccessDeniedException('you have to be logged in ');

        }
        $bucket=$order->findOneBy([
            'client'=>$this->client
        ]);
        
        return $this->render('bucket/index.html.twig', [
            'panier' => $bucket,
        ]);
    }
    #[Route('/add/{id}',name:'add')]
    public function addProduct(Request $request,OrderManager $om,Product $product){
        $quantity=$request->request->getInt('quantite',1);
        $order=$om->createOrderForCLient($this->client);
        $om->addProductToBucket($order,$product,$quantity);
        $this->addFlash('success','Product added to bucket ');
        return $this->redirectToRoute('client.bucket.home');
    }
}
