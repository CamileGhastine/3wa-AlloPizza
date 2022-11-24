<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Cart\CartHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/order', name: 'order')]
    public function index(ProductRepository $productRepository, CartHandler $cartHandler): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findBy([], ['price' => 'ASC']),
            'cart' => $cartHandler->getCart()
        ]);
    }
}
