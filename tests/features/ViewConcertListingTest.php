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

        $response = $this->get('/concerts/'.$concert->id);
        $response->assertStatus(200);
        $response->assertSee('Concert Title');
        $response->assertSee('Concert Sub Title');
        $response->assertSee('December 13, 2019');
        $response->assertSee('8:00pm');
        $response->assertSee('20.00');
        $response->assertSee('The venue');
        $response->assertSee('123 example lane');
        $response->assertSee('Laraville');
        $response->assertSee('ON');
        $response->assertSee('17926');
        $response->assertSee('for tickets, call (555) 555 555');
    }

    /**
     * @test
     */

     function user_cant_view_unpublished_concerts()
     {
        $concert = factory(Concert::class)->create([
            'published_at' => null,
        ]);

        $response = $this->get('/concerts/'.$concert->id);
        
        $response->assertStatus(404);
     }
}
