<?php


namespace Tests\Unit\Billing;


use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Token;
use Tests\TestCase;

/**
 * Class StripePaymentGatewayTest
 * @package Tests\Unit\Billing
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    /**
     * @var Charge
     */
    private $lastCharge;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->lastCharge = $this->lastCharge();
        } catch (ApiErrorException $e) {
        }
    }

    /** @test
     * @throws ApiErrorException
     */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new StripePaymentGateway();

        $paymentGateway->charge(2500, $this->validToken());

        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(2500, $this->lastCharge()->amount);
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail()
    {
        try {
            $paymentGateway = new StripePaymentGateway();
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertNotNull($e);
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail("Charging with an invalid payment token, did not throw an exception");
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     */
    private function lastCharge(): Charge
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

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

    private function newCharges(): array
    {
        return Charge::all([
            'limit' => 1,
            'ending_before' => $this->lastCharge->id
        ])['data'];
    }
}