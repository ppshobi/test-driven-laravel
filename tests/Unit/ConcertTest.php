<?php
namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;
    
    /**
     * @test
     */

    function it_can_get_formatted_date() 
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals("December 1, 2016", $concert->formatted_date);
    }
    
    /**
     * @test
     */

    function it_can_get_formatted_start_time() 
    {
       $concert = factory(Concert::class)->make([
           'date' => Carbon::parse('2016-12-01 17:00:00'),
       ]);

       $this->assertEquals("5:00pm", $concert->formatted_start_time);
    }
    /**
     * @test
     */

    function can_get_ticket_price_in_dollars() 
    {
       $concert = factory(Concert::class)->make([
           'ticket_price' => 6750,
       ]);

       $this->assertEquals("67.50", $concert->ticket_price_in_dollars);
    }

    /**
     * @test
     */

     function concerts_with_a_published_date_are_published()
     {
        $publishedConcertA = factory(Concert::class)->create(['published_at' => Carbon::now()->subWeek()]);
        $publishedConcertB = factory(Concert::class)->create(['published_at' => Carbon::parse('-2 week')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();
        
        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));

     }

    /**
     * @test
     **/
    function can_add_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(50);
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /**
     * @test
     **/
    function tickets_remaining_does_not_include_tickets_associated_with_orders()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class,30)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class,20)->create(['order_id' => null]));

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /**
     * @test
     **/
    function trying_to_reserve_more_than_remaining_tickets_throws_an_exception()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);

        try {
            $reservation = $concert->reserveTickets(11, 'jane@example.org');
        } catch (NotEnoughTicketsException $e) {

            $this->assertFalse($concert->hasOrderFor('jane@example.org'));

            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Order succeeded even though there are not enough tickets");
    }

    /**
     * @test
     **/
    function can_reserve_available_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(3);

        $reservation = $concert->reserveTickets(2, 'jhon@example.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals(1, $concert->ticketsRemaining());
        $this->assertEquals('jhon@example.com', $reservation->email());
    }

    /**
     * @test
     **/
    function cant_reserve_already_purchased_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $order = factory(Order::class)->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        try{
            $concert->reserveTickets(2, 'jhon@example.com');
        }catch (NotEnoughTicketsException $e){
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Reserving tickets succeeded, even though there are not enough tickets");
    }

    /**
     * @test
     **/
    function cant_reserve_tickets_that_are_already_reserved_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(3);
        $concert->reserveTickets(2, 'jane@example.com');

        try{
            $concert->reserveTickets(2, 'jhon@example.com');
        }catch (NotEnoughTicketsException $e){
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Reserving tickets succeeded, even though they are reserved");
    }
}
