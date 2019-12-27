<?php
namespace Tests\Unit;

use Mockery;
use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use App\Billing\Charge;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     **/
    function creating_an_order_from_tickets_email_and_charge()
    {
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);
        $charge = new Charge([
            'amount' => 3600,
            'card_last_four' => '1234',
        ]);
        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);

        $tickets->each->shouldHaveReceived('claimFor', [$order]);
    }

    /**
     * @test
     **/
    function retrieving_an_order_by_order_confirmation_number()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /**
     * @test
     **/
    function retrieving_a_non_existent_order_by_confirmation_number_throws_an_exception()
    {
        try {
            Order::findByConfirmationNumber('NONEXISTENTCONFIRMATION');
        } catch (ModelNotFoundException $e) {
            return;
        }

        $this->fail('No Matching Order with confirmation was found but it didnt threw an exception');
    }
    /**
     * @test
     **/
    function converting_to_array()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'           => 'jane@example.com',
            'amount'          => 6000,
        ]);
        $order->tickets()->saveMany([
            factory(Ticket::class)->create(['code' => 'TICKETCODE1']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE2']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE3']),
        ]);
        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'           => 'jane@example.com',
            'amount'          => 6000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ], $result);
    }
}