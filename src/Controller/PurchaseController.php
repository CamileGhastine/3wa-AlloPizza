<?php

namespace App\Controller;

use App\Cart\CartEntitiesPersister;
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
    public function purchaseConfirmation(SessionInterface $session, CartHandler $cartHandler): Response
    {
        if($this->checkRequirement($session)) {
            $routeName = $this->checkRequirement($session);

            return $this->redirectToRoute($routeName);
        }

        if(!$session->get('purchase')) {
            $this->addFlash('danger', "Vous devez remplir les informations de livraison");

            return $this->redirectToRoute('purchase');
        }

        \Stripe\Stripe::setApiKey('sk_test_51M3hC4KOvMqC5i9eIxaszMTpxRv4QlrlA8hat5Fx9FYcSeWEHwB4m0QVw5aDRtKCbZyQCJncpluRTBh9JpxMBhOk00q1mQ4yUr');

        // Create a PaymentIntent with amount and currency
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $session->get('purchase')->getAmount(),
            'currency' => 'eur',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        return $this->render('purchase/payment.html.twig', [
            'cart' => $cartHandler->getCart(),
            'purchase' => $session->get('purchase'),
            'clientSecret' => $paymentIntent->client_secret
        ]);
    }

    #[Route('/paymentSuccess', name: 'payment_success')]
    public function paymentSucess(): Response
    {
        dd('payement réussi');
    }

    private function checkRequirement(SessionInterface $session)
    {
        if(!$this->getUser()) {
            $this->addFlash('danger', "Vous devez vous connecter pour commander.");

            return 'app_login';
        }

        if(!$session->get('cart')) {
            $this->addFlash('danger', "Votre panier est vide");

            return 'order';
        }

        return false;
    }
}
