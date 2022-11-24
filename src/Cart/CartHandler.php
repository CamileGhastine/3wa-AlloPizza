<?php

namespace App\Cart;

use App\Entity\PurchaseItem;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    private $session;

    public function __construct(private ProductRepository $productRepository, RequestStack $requestStack) {
        $this->session = $requestStack->getSession();
    }

    public function add(int $id)
    {
        $cart = $this->session->get('cart', []);

        if(array_key_exists($id, $cart)) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function sub($id)
    {
        $cart = $this->session->get('cart', []);

        if(!array_key_exists($id, $cart)) {
            return;
        }

        $cart[$id]--;

        if($cart[$id] <= 0) {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function removeItem($id)
    {
        $cart = $this->session->get('cart', []);

        if(!array_key_exists($id, $cart)) {
            return;
        }

        unset($cart[$id]);

        $this->session->set('cart', $cart);

    }

    public function empty()
    {
        $this->session->remove('cart');
    }

    public function getCart()
    {
        $order= [];
        $total = 0;

        foreach ($this->session->get('cart', []) as $id => $quantity) {
            $product = $this->productRepository->find($id);

            $purchaseItem = new PurchaseItem();
            $purchaseItem->setProduct($product)
                ->setQuantity($quantity)
                ->setProductName($product->getName())
                ->setProductPrice($product->getPrice())
            ;

            $order[$id] = $purchaseItem ;

            $total += $product->getPrice() * $quantity;
        }

       return new Cart($order,$total);
    }
}
