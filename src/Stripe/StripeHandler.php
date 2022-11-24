<?php

namespace App\Stripe;

use App\Entity\Purchase;

class StripeHandler
{
    public function __construct(private $stripeSecret, private $stripePublic) {
    }



    public function createPaymentIntent(Purchase $purchase)
    {
        \Stripe\Stripe::setApiKey($this->stripeSecret);

        // Create a PaymentIntent with amount and currency
        return \Stripe\PaymentIntent::create([
            'amount' => $purchase->getAmount(),
            'currency' => 'eur',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }

    public function getStripePublic()
    {
        return $this->stripePublic;
    }
}