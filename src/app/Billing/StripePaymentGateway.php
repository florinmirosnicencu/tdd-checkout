<?php


namespace App\Billing;


use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;
use Stripe\Token;

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

    public function getValidTestToken(): String
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ])->id;
    }

    public function newChargesDuring($callback): object
    {
        $latestCharge = $this->lastCharge();
        $callback($this);
        return $this->newChargesSince($latestCharge)->pluck('amount');
    }

    private function lastCharge()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        return Charge::all([
            'limit' => 1,
        ])['data'][0];
    }

    private function newChargesSince(?Charge $charge): object
    {
        $newCharges = Charge::all([
            'limit' => 1,
            'ending_before' => $charge ? $charge->id : null
        ])['data'];

        return collect($newCharges);
    }
}