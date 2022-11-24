<?php

namespace App\Controller;

use App\Cart\CartEntitiesPersister;
use App\Cart\CartHandler;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Form\PurchaseType;
use App\Stripe\StripeHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase', name: 'purchase')]
    public function index(CartHandler $cartHandler, Request $request, SessionInterface $session, CartEntitiesPersister $persister): Response
    {
        if($this->checkRequirement($session)) {
            $routeName = $this->checkRequirement($session);

            return $this->redirectToRoute($routeName);
        }

        $purchase = new Purchase($this->getUser());
        $purchase->setAmount($cartHandler->getCart()->getTotal());

        $purchaseForm = $this->createForm(PurchaseType::class, $purchase);

        $purchaseForm->handleRequest($request);

        if ($purchaseForm->isSubmitted() && $purchaseForm->isValid()) {

            $persister->persist($purchase);

            $session->set('purchase', $purchase);

            return $this->redirectToRoute('purchase_confirmation');
        }

        return $this->render('purchase/index.html.twig', [
            'cart' => $cartHandler->getCart(),
            'purchaseForm' => $purchaseForm->createView()
        ]);
    }

    #[Route('/purchaseConfirmation', name: 'purchase_confirmation')]
    public function purchaseConfirmation(SessionInterface $session, CartHandler $cartHandler, StripeHandler $stripe): Response
    {
        if($this->checkRequirement($session, true)) {
            $routeName = $this->checkRequirement($session);

            return $this->redirectToRoute($routeName);
        }

        $paymentIntent = $stripe->createPaymentIntent($session->get('purchase'));

        return $this->render('purchase/payment.html.twig', [
            'cart' => $cartHandler->getCart(),
            'purchase' => $session->get('purchase'),
            'clientSecret' => $paymentIntent->client_secret,
            'publicKey' => $stripe->getStripePublic()
        ]);
    }

    #[Route('/paymentSuccess/{id<[0-9]+>}', name: 'payment_success')]
    public function paymentSucess(Purchase $purchase, SessionInterface $session, EntityManagerInterface $em): Response
    {
        if($this->checkRequirement($session, true)) {
            $routeName = $this->checkRequirement($session);

            return $this->redirectToRoute($routeName);
        }

        $purchase->setStatus(Purchase::STATUS_PAID);
        $em->flush();

        $session->remove('cart');
        $session->remove('purchase');

//        TO DO
//        $mail->sendMail();

        $this->addFlash('success', 'Votre commande a été payé avec succès. Consultez votre mail de confirmation.');
        return $this->redirectToRoute('home');
    }

    private function checkRequirement(SessionInterface $session, $checkPurchase = false)
    {
        if(!$this->getUser()) {
            $this->addFlash('danger', "Vous devez vous connecter pour commander.");

            return 'app_login';
        }

        if(!$session->get('cart')) {
            $this->addFlash('danger', "Votre panier est vide");

            return 'order';
        }

        if(!$session->get('purchase') && $checkPurchase) {
            $this->addFlash('danger', "Vous devez remplir les informations de livraison");

            return $this->redirectToRoute('purchase');
        }

        return false;
    }
}
