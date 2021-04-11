<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Tests\TestCase;
/*
 * @group Unit
 */
class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;

    /** @test */
    public function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = $this->getPaymentGateway();
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->getTotalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(5000, $paymentGateway->getTotalCharges());
        $this->assertEquals(1, $timesCallbackRan);
    }

    protected function getPaymentGateway(): PaymentGateway
    {
        return new FakePaymentGateway();
    }

}