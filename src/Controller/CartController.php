<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Cart\CartHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(
        private CartHandler $cartHandler,
        private ProductRepository $productRepository
    ) {}

    #[Route('/cart/add/{id<[0-9]+>}', name: 'add_cart')]
    public function add(int $id): Response
    {
        if(!$this->productRepository->find($id)) {
            throw $this->createNotFoundException("ce produit n'existe pas");
        }

        $this->cartHandler->add($id);

        return $this->redirectToRoute('order');
    }

    #[Route('/cart/sub/{id<[0-9]+>}', name: 'sub_cart')]
    public function sub(int $id, SessionInterface $session, ProductRepository $productRepository): Response
    {
        if(!$productRepository->find($id)) {
            throw $this->createNotFoundException("ce produit n'xiste pas");
        }

        $this->cartHandler->sub($id);

        return $this->redirectToRoute('order');
    }

    #[Route('cart/remove/{id<[0-9]+>}', name: 'remove_item')]
    public function removeItem(int $id, SessionInterface $session, ProductRepository $productRepository)
    {
        if(!$productRepository->find($id)) {
            throw $this->createNotFoundException("ce produit n'xiste pas");
        }

        $this->cartHandler->removeItem($id);

        return $this->redirectToRoute('order');
    }

    #[Route('cart/remove', name: 'remove_cart')]
    public function remove()
    {
        $this->cartHandler->empty();

        return $this->redirectToRoute('order');
    }
}
