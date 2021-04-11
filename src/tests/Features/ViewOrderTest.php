<?php


namespace Tests\Features;


use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
/**
 * @group Feature
 */
class ViewOrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        $this->withoutExceptionHandling();
        //create a concert
        $concert = factory(Concert::class)->create();
        //create an order
        $order = factory(Order::class)->create(
            [
                'confirmation_number' => 'ORDERCONFIRMATION12345',
                'card_last_four' => '1881',
                'amount' => 8500
            ]
        );
        //create some tickets
        $ticket = factory(Ticket::class)->create(
            [
                'concert_id' => $concert->id,
                'order_id' => $order->id,
            ]
        );

        //visit the order confirmation page
        $response = $this->get('/orders/ORDERCONFIRMATION12345');

        //assert we see the correct order details
        $response->assertOk();

        $response->assertViewHas('order', function ($viewOrder) use ($order) {
            return $order->id === $viewOrder->id;
        });

        $response->assertSee('ORDERCONFIRMATION12345');
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** 1881');
    }

}