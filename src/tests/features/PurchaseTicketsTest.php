<?php


use App\Billing\FakePaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function customer_can_purchase_concert_tickets()
    {
        $paymentGateway = new FakePaymentGateway();
        $this->app->instance(\App\Billing\PaymentGateway::class, $paymentGateway);

        $concert = factory(Concert::class)->create(
            [
                'ticket_price' => 3250,
            ]
        );

        $this->json('post', '/concerts/' . $concert->id . '/orders', [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);

        $this->assertEquals(9750, $paymentGateway->getTotalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /**
     * @test
     */
    public function email_is_required_to_purchase_tickets()
    {
        $paymentGateway = new FakePaymentGateway();
        $this->app->instance(\App\Billing\PaymentGateway::class, $paymentGateway);
        $concert = factory(Concert::class)->create();

        $this->json('post', '/concerts/' . $concert->id . '/orders', [
            'ticket_quantity' => 3,
            'payment_token'   => $paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
    }
}