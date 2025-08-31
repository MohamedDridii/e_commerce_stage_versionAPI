<?php

namespace App\Controller\Client;

use App\Entity\Product;
use App\Entity\Order;
use App\Entity\Client;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Services\BucketSession;
use App\Services\CreateOrder_OrderLine;
use App\Services\OrderManager;
use App\Services\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Node\Expression\ReturnNumberInterface;

#[Route('client/bucket', name: 'client.bucket.')]
final class BucketController extends AbstractController
{
    private ?Client $client=null;
    private function getClient(): Client // Changez le type de retour en Client
    {
        if (!$this->client) {
            $user = $this->getUser();
            
            if (!$user) {
                throw $this->createAccessDeniedException('You must be logged in');
            }
            
            if (!$user instanceof Client) {
                throw $this->createAccessDeniedException('Invalid user type');
            }
            
            $this->client = $user;
        }
        
        return $this->client;
    }
    
    //this is the part where we show all products in the bucket 
    #[Route('/home', name: 'home')]
    public function index(BucketSession $bucketSession,ProductRepository $productRepo): Response
    {
        $bucketData=$bucketSession->getBucket();
        $items=[];
        $total=0;
        foreach($bucketData as $productId=>$quantity){
            $product=$productRepo->find($productId);
            if($product){
                $souTotal=$product->getPrice()*$quantity;
                $items[]=[
                    'product'=>$product,
                    'quantity'=>$quantity,
                    'sousTotal'=>$souTotal
                ];
                $total+=$souTotal;
            }
        }
        return $this->render('bucket/index.html.twig', [
            'items' => $items,
            'total'=>$total
        ]);
    }
    //this is the part where we're going to add a product to the bucket it will be a button in the show product details page with a quantity field 
    #[Route('/add/{id}',name:'add')]
    public function addProduct(Request $request,BucketSession $bucketSession,Product $product)
    {
        $quantity=$request->request->getInt('quantite',1);
        $bucketSession->addProductToBucket($product->getId(),$quantity);
        $this->addFlash('success', 'Product added to Bucket');


        return $this->redirectToRoute('client.bucket.home');
    }

    
    //this is where we're gonna delete a product it will be in the home bucket page a button when clicked it executes this method 
    #[Route('/remove/{id}',name:'remove')]
    public function removeProduct (Product $product,BucketSession $bucketSession){
        $bucketSession->removeProduct($product->getId());
        $this->addFlash('success','Product removed ');
        return $this->redirectToRoute('client.bucket.home');
    }


    //this method will be exectued when clicking on button update in the bucket home page 
    #[Route('/update/{id}',name:'update')]
    public function updateProduct (Product $product,BucketSession $bucketSession,Request $request){
        $quantity=$request->request->getInt('quantite',1);
        $bucketSession->updateQuantity($product->getId(),$quantity);
        $this->addFlash('success','Product updated  ');
        return $this->redirectToRoute('client.bucket.home');
    }


    #[Route('/validate',name:'validate')]
    public function validateBucket(BucketSession $bucketSession,EntityManagerInterface $em,ProductRepository $productRepo,CreateOrder_OrderLine $create)
    {
        $client = $this->getClient();
        $bucketData=$bucketSession->getBucket();
        if(empty($bucketData))
        {
            $this->addFlash('error','Your Bucket is empty');
            return $this->redirectToRoute('client.bucket.home');
        }
        
        //verify if the quantity is availbel or  not and create order 
        foreach($bucketData as $productId=>$quantity)
        {
            $product=$productRepo->find($productId);
            $availble=$productRepo->countTotalStock($product);
            if($availble<$quantity)
            {
                $this->addFlash('danger','Product '.$product->getName().' is not available in quantity '.$quantity);
                return $this->redirectToRoute('client.product.home');
            }
        }
        //create order 
        $order=$create->createOrder($client);

        foreach ($bucketData as $productId => $qtyNeeded)
        {
            $product = $productRepo->find($productId);
            $create->createOrderLine($order, $product, $qtyNeeded);

            // Clone the collection so we can iterate safely while modifying
            $stocks = new ArrayCollection($product->getStocks()->toArray());

            $qtyLeft = $qtyNeeded;
            foreach ($stocks as $stock) 
            {
                if ($qtyLeft <= 0) break;

                $qtyInRow = $stock->getQuantity();
                $deduct   = min($qtyLeft, $qtyInRow);

                $stock->setQuantity($qtyInRow - $deduct);
                $qtyLeft -= $deduct;
            }
        }        
        $em->flush();
        $bucketSession->clear();
        $this->addFlash('success','Order Validated!!');
        return $this->redirectToRoute('client.bucket.confirmation',[
            'id'=>$order->getId()
        ]);
    }

    
    #[Route('/confirmation/{id}', name: 'confirmation')]
    public function confirmation(Order $order): Response
    {
        return $this->render('bucket/confirmation.html.twig', [
            'commande' => $order
        ]);
    }
    #[Route('/invoice/{id}', name: 'invoice')]
    public function invoice(Order $order, PdfGenerator $pdfGenerator): Response
    {
        // Utilise un template Twig pour générer le HTML de la facture
        $html = $this->renderView('bucket/invoice.html.twig', [
            'commande' => $order
        ]);

        // Génère et télécharge le PDF
        return $pdfGenerator->generatePdf($html, 'facture-'.$order->getNumeroCommande().'.pdf');
    }
}