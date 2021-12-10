<?php


namespace Tests\Unit\Billing;


use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Tests\TestCase;

/**
 * Class StripePaymentGatewayTest
 * @package Tests\Unit\Billing
 * @group Integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;


    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500, $newCharges->sum());
    }

    protected function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway();
    }
}