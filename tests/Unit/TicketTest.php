<?php
namespace Tests\Unit;


use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Facades\TicketCode;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     **/
    function ticket_can_be_released()
    {
        $ticket = factory(Ticket::class)->states('reserved')->create();

        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /**
     * @test
     **/
    function a_ticket_can_be_reserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();
        $this->assertNotNull($ticket->fresh()->reserved_at);

    }

    /**
     * @test
     **/
    function a_ticket_can_be_claimed_for_an_order()
    {
        $order = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create(['code' => null]);
        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');
        $ticket->claimFor($order);

        $this->assertContains($ticket->id, $order->tickets->pluck('id'));
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
