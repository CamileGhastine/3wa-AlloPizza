<?php

namespace App\Cart;

use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use Doctrine\ORM\EntityManagerInterface;

class CartEntitiesPersister
{
    public function __construct(private EntityManagerInterface $em, private CartHandler $cartHandler) {}

    public function persist(Purchase $purchase)
    {
        $this->em->persist($purchase);
        foreach($this->cartHandler->getCart()->getOrder() as $purchaseItem) {
            $purchaseItem->setPurchase($purchase);
            $this->em->persist($purchaseItem);
        }
        $this->em->flush();
    }
}