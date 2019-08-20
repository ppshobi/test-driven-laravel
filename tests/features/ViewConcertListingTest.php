<?php

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * @test
     */
    function user_can_view_a_published_concert_listing()
    {
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2019-12-13 8:00pm'),
            'published_at' => Carbon::now()->subWeek(),
        ]);

        $this->visit('/concerts/'.$concert->id);

        $this->see('Concert Title');

        $this->see('Concert Sub Title');
        $this->see('December 13, 2019');
        $this->see('8:00pm');
        $this->see('32.50');
        $this->see('The venue');
        $this->see('123 example lane');
        $this->see('Laraville');
        $this->see('ON');
        $this->see('17926');
        $this->see('for tickets, call (555) 555 555');
    }

    /**
     * @test
     */

     function user_cant_view_unpublished_concerts()
     {
        $concert = factory(Concert::class)->create([
            'published_at' => null,
        ]);

        $this->get('/concerts/'.$concert->id);
        
        $this->assertResponseStatus(404);
     }
}
