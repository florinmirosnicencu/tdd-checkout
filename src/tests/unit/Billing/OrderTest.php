<?php


use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function converting_to_array()
    {
        $concert = factory(\App\Concert::class)->create(
            [
                'ticket_price' => 1200
            ]
        )->addTickets(5);
        $order = $concert->orderTickets('jane@example.com', 5);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount' => 6000
        ], $result);
    }

    /**
     * @test
     */
    public function test_the_tickets_are_released_when_the_order_is_canceled()
    {
        $concert = factory(\App\Concert::class)->create()->addTickets(10);
        $order = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();
        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));

    }

}