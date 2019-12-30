<?php


use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function customer_can_purchase_concert_tickets()
    {
        $concert = factory(\App\Concert::class)->create(
            [
                'ticket_price' => 3250,
            ]
        );

        $this->json('post', '/concerts/' . $concert->id . '/orders', [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(9750, $paymentGateway->getTotalCharges());

        $order = $concert->order->where('email', 'john@example.com')->first();

        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets->count());
    }

}