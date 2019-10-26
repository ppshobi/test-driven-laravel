<?php

use App\Order;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     **/
    function ticket_can_be_released()
    {
        $concert = factory(Concert::class)->create()->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);

        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}
