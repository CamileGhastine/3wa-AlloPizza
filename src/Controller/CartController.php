<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Cart\CartHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(
        private CartHandler $cartHandler,
        private ProductRepository $productRepository
    ) {

    }

    #[Route('/cart/add/{id<[0-9]+>}', name: 'add_cart')]
    public function add(int $id, Request $request): Response
    {
        if(!$this->productRepository->find($id)) {
            throw $this->createNotFoundException("ce produit n'existe pas");
        }

        $route = $request->headers->get('referer');

        $this->cartHandler->add($id);

        return $this->redirect($route);
    }

    #[Route('/cart/sub/{id<[0-9]+>}', name: 'sub_cart')]
    public function sub(int $id, ProductRepository $productRepository, Request $request): Response
    {
        if(!$productRepository->find($id)) {
            throw $this->createNotFoundException("ce produit n'xiste pas");
        }

        $route = $request->headers->get('referer');

        $this->cartHandler->sub($id);

        return $this->redirect($route);
    }

    #[Route('cart/remove/{id<[0-9]+>}', name: 'remove_item')]
    public function removeItem(int $id, ProductRepository $productRepository, Request $request)
    {
        if(!$productRepository->find($id)) {
            throw $this->createNotFoundException("ce produit n'xiste pas");
        }

        $route = $request->headers->get('referer');

        $this->cartHandler->removeItem($id);

        return $this->redirect($route);
    }

    #[Route('cart/remove', name: 'remove_cart')]
    public function remove()
    {
        $this->cartHandler->empty();

        return $this->redirectToRoute('order');
    }

    #[Route('ajax', name: 'ajax')]
    public function ajax(Request $request, CartHandler $cartHandler)
    {
        $id = $request->request->get('id');
        $action = $request->request->get('action');

        $cartHandler->$action($id);

        return $this->render('product/shared/_cart.html.twig', [
            'cart' => $cartHandler->getCart()
        ]);
    }
}
