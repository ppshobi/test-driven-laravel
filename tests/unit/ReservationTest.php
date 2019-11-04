<?php

use App\Ticket;
use App\Reservation;

class ReservationTest extends TestCase
{
    /**
     * @test
     **/
    function calculating_the_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }

    /**
     * @test
     **/
    function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        $tickets = collect([
            Mockery::mock(Ticket::class, function ($mock){
                 $mock->shouldReceive('release')->once();
            }),
            Mockery::mock(Ticket::class, function ($mock){
                 $mock->shouldReceive('release')->once();
            }),
            Mockery::mock(Ticket::class, function ($mock){
                 $mock->shouldReceive('release')->once();
            }),
        ]);
        $reservation = new Reservation($tickets);

        $reservation->cancel();
    }
}
