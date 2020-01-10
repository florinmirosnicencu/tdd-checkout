<?php


namespace App\Billing;


use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class StripePaymentGateway implements PaymentGateway
{
    /**
     * @var string
     */
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('STRIPE_SECRET');
        Stripe::setApiKey($this->apiKey);
    }

    public function charge(int $amount, string $token): void
    {
        try {
            Charge::create(
                [
                    'amount' => $amount,
                    'source' => $token,
                    'currency' => 'usd'
                ]
            );
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException;
        }

    }
}