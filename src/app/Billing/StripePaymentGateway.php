<?php


namespace App\Billing;


use Stripe\Charge;
use Stripe\Stripe;

class StripePaymentGateway implements PaymentGateway
{
    /**
     * @var string
     */
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        Stripe::setApiKey($this->apiKey);
    }

    public function charge(int $amount, string $token): void
    {
        Charge::create(
            [
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd'
            ]
        );
    }
}