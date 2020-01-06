<?php


namespace Tests\Unit\Billing;


use Stripe\Stripe;
use Stripe\Token;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new StripePaymentGateway();

        Stripe::setApiKey('sk_test_a2Af9Av8MK9CkPpmpEhi48IJ');

        $token = Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ]);

        $paymentGateway->charge(2500, $validToken);
    }
}