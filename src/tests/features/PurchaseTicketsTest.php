<?php


use App\Billing\FakePaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var FakePaymentGateway
     */
    private $paymentGateway;

    protected function setUp()
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(\App\Billing\PaymentGateway::class, $this->paymentGateway);
    }

    /**
     * @test
     */
    public function customer_can_purchase_published_concert_tickets()
    {
        $concert = factory(Concert::class)->states(['published'])->create(['ticket_price' => 3250,])->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);

        $this->assertEquals(9750, $this->paymentGateway->getTotalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /**
     * @test
     */
    public function cannot_purchase_tickets_for_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states(['unpublished'])->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->getTotalCharges());
    }

    /**
     * @test
     */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states(['published'])->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    /**
     * @test
     */
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states(['published'])->create();

        $this->orderTickets($concert, [
            'email'           => 'not-a-valid-email',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    /**
     * @test
     */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states(['published'])->create();

        $this->orderTickets($concert, [
            'email'         => 'john@test.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /**
     * @test
     */
    public function ticket_quantity_is_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states(['published'])->create();

        $this->orderTickets($concert, [
            'email'           => 'john@test.com',
            'ticket_quantity' => 0,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /**
     * @test
     */
    public function payment_token_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states(['published'])->create();

        $this->orderTickets($concert, [
            'email'           => 'john@test.com',
            'ticket_quantity' => 3,
        ]);

        $this->assertValidationError('payment_token');
    }

    /**
     * @test
     */
    public function can_not_purchase_more_tickets_than_remain()
    {
        $concert = factory(Concert::class)->states(['published'])->create();
        $concert->addTickets(50);

        $this->orderTickets($concert, [
            'email'           => 'john@test.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
        $order = $concert->orders()->where('email', 'john@test.com')->first();
        $this->assertNull($order);
        $this->assertEquals(0, $this->paymentGateway->getTotalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());

    }

    /**
     * @test
     */
    public function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->states(['published'])->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNull($order);

    }

    private function orderTickets($concert, $params)
    {
        $this->json('post', '/concerts/' . $concert->id . '/orders', $params);
    }

    private function assertValidationError($field): void
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }
}