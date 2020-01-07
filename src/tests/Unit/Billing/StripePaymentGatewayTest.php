<?php


namespace Tests\Unit\Billing;


use App\Billing\StripePaymentGateway;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Token;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    const TEST_STRIPE_API_KEY = 'sk_test_a2Af9Av8MK9CkPpmpEhi48IJ';

    /** @test
     * @throws ApiErrorException
     */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new StripePaymentGateway(self::TEST_STRIPE_API_KEY);
        $lastCharge = $this->lastCharge();

        $paymentGateway->charge(2500, $this->validToken());

        $newCharge = Charge::all([
            'limit' => 1,
            'ending_before' => $lastCharge->id
        ])['data'][0];

        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(2500, $newCharge->amount);
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     */
    private function lastCharge(): string
    {
        return Charge::all([
            'limit' => 1,
        ])['data'][0];
    }

    /**
     * @return string
     * @throws ApiErrorException
     */
    private function validToken(): string
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

    private function newCharges($endingBefore)
    {
    }
}