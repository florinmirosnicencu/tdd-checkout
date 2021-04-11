<?php

namespace Tests\Features;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
/**
 * @group Feature
 */
class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var FakePaymentGateway
     */
    private $paymentGateway;
    /**
     * @var TestResponse
     */
    private TestResponse $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function customer_can_purchase_published_concert_tickets()
    {
        $this->withoutExceptionHandling();

        $concert = Concert::factory()->published()->create(['ticket_price' => 3250,])->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);
        $this->response->decodeResponseJson()->assertExact([
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'amount' => 9750,
        ]);

        $this->assertEquals(9750, $this->paymentGateway->getTotalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
    }

    /** @test */
    public function cannot_purchase_tickets_for_an_unpublished_concert()
    {
        $concert = Concert::factory()->unpublished()->create()->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);

        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->getTotalCharges());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $this->orderTickets($concert, [
            'email' => 'not-a-valid-email',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $this->orderTickets($concert, [
            'email' => 'john@test.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_is_at_least_1_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $this->orderTickets($concert, [
            'email' => 'john@test.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    public function payment_token_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $this->orderTickets($concert, [
            'email' => 'john@test.com',
            'ticket_quantity' => 3,
        ]);

        $this->assertValidationError('payment_token');
    }

    /** @test */
    public function can_not_purchase_more_tickets_than_remain()
    {
        $concert = Concert::factory()->published()->create()->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@test.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@test.com'));
        $this->assertEquals(0, $this->paymentGateway->getTotalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());

    }

    /** @test */
    public function can_not_purchase_tickets_another_customer_is_trying_to_purchase()
    {
        $this->withoutExceptionHandling();
        $concert = Concert::factory()->published()->create(['ticket_price' => 1200])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
            $this->orderTickets($concert, [
                'email' => 'personB@test.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);

            $this->assertResponseStatus(422);
            $this->assertFalse($concert->hasOrderFor('personB@test.com'));
        });


        $this->orderTickets($concert, [
            'email' => 'personA@test.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(3600, $this->paymentGateway->getTotalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@test.com'));
        $this->assertEquals(3, $concert->ordersFor('personA@test.com')->first()->ticketQuantity());
    }

    /** @test */
    public function an_order_is_not_created_if_payment_fails()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    private function orderTickets($concert, $params)
    {
        $savedRequest = $this->app['request'];
        $this->response = $this->json('post', '/concerts/' . $concert->id . '/orders', $params);
        $this->app['request'] = $savedRequest;
    }

    private function assertResponseStatus($status)
    {
        $this->response->assertStatus($status);
    }

    private function assertValidationError($field): void
    {
        $error = $this->response->decodeResponseJson();
        $error = $error['errors'];

        $this->assertArrayHasKey($field, $error);
    }
}