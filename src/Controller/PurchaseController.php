<?php

namespace App\Controller;

use App\Cart\CartHandler;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Form\PurchaseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase', name: 'purchase')]
    public function index(CartHandler $cartHandler, Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {
        if(!$this->getUser()) {
            $this->addFlash('danger', "Vous devez vous connecter pour commander.");

            return $this->redirectToRoute('app_login');
        }

        if(!$session->get('cart')) {
            $this->addFlash('danger', "Votre panier est vide");

            return $this->redirectToRoute('order');
        }

        $cart = $cartHandler->getCart();

        $purchase = new Purchase($this->getUser());
        $purchase->setAmount($cart->getTotal());

        $purchaseForm = $this->createForm(PurchaseType::class, $purchase);

        $purchaseForm->handleRequest($request);

        if ($purchaseForm->isSubmitted() && $purchaseForm->isValid()) {

            $em->persist($purchase);
            foreach($cart->getOrder() as $item) {
                $purchaseItem = new PurchaseItem();
                $purchaseItem->setProduct($item['product'])
                    ->setQuantity($item['quantity'])
                    ->setProductName($item['product']->getName())
                    ->setProductPrice($item['product']->getPrice())
                    ->setPurchase($purchase)
                ;
                $em->persist($purchaseItem);
            }
            $em->flush();

            $session->set('purchase', $purchase);

            return $this->redirectToRoute('purchase_confirmation');
        }

        return $this->render('purchase/index.html.twig', [
            'cart' => $cart,
            'purchaseForm' => $purchaseForm->createView()
        ]);
    }

    #[Route('/purchaseConfirmation', name: 'purchase_confirmation')]
    public function purchaseConfirmation(SessionInterface $session, CartHandler $cartHandler): Response
    {
        if(!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if(!$session->get('cart')) {
            $this->addFlash('danger', "Votre panier est vide");

            return $this->redirectToRoute('order');
        }

        if(!$session->get('purchase')) {
            $this->addFlash('danger', "Vous devez remplir les informations de livraison");

            return $this->redirectToRoute('purchase');
        }

        return $this->render('purchase/payment.html.twig', [
            'cart' => $cartHandler->getCart(),
            'purchase' => $session->get('purchase')

        ]);
    }
}
