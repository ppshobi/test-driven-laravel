<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    private function validParams($overRides = [])
    {
        return array_merge([
            'title' => 'The red chord',
            'subtitle' => 'with animosity & lethargy',
            'additional_information' => 'this concert is 19+',
            'venue' => 'The mosh pit',
            'venue_address' => '123 example lane',
            'city' => 'laraville',
            'state' => 'ON',
            'zip' => '673565',
            'date' =>'2017-11-18',
            'time' =>'8:00pm',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ], $overRides);
    }

    /**
     * @test
     **/
    function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /**
     * @test
     **/
    function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

     /**
     * @test
     **/
    function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $otherConcert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$otherConcert->id}/edit");

        $response->assertStatus(404);
    }

    /**
     * @test
     **/
    function promoters_see_a_404_response_when_attempting_to_view_the_edit_form_for_a_non_existing_concert()
    {
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");
        $response->assertStatus(404);
    }

    /**
     * @test
     **/
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
        $otherUser    = factory(User::class)->create();
        $otherConcert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->get("/backstage/concerts/{$otherConcert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

     /**
     * @test
     **/
    function  guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_non_existent_concert()
    {
        $user     = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     **/
    function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id'                =>  $user->id,
            'title'                  => 'Old Concert Title',
            'subtitle'               => 'Old Concert Sub Title',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'ticket_price'           => 2000,
            'venue'                  => 'Old The venue',
            'venue_address'          => 'Old 123 example lane',
            'city'                   => 'Old Laraville',
            'state'                  => 'Old ON',
            'zip'                    => '000000',
            'additional_information' => 'Old for tickets, call (555) 555 555',
            'published_at'           => null,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'new Concert Title',
            'subtitle'               => 'new Concert Sub Title',
            'date'                   => '2019-01-01',
            'time'                   => '8:00pm',
            'ticket_price'           => '72.50',
            'venue'                  => 'new The venue',
            'venue_address'          => 'new 123 example lane',
            'city'                   => 'new Laraville',
            'state'                  => 'new ON',
            'zip'                    => '999999',
            'additional_information' => 'new for tickets, call (555) 555 555',
        ]);

        $response->assertRedirect('/backstage/concerts');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('new Concert Title', $concert->title);
            $this->assertEquals('new Concert Sub Title', $concert->subtitle);
            $this->assertEquals(Carbon::parse('2019-01-01 8:00pm'), $concert->date);
            $this->assertEquals(7250, $concert->ticket_price);
            $this->assertEquals('new The venue', $concert->venue);
            $this->assertEquals('new 123 example lane', $concert->venue_address);
            $this->assertEquals('new Laraville', $concert->city);
            $this->assertEquals('new ON', $concert->state);
            $this->assertEquals('999999', $concert->zip);
            $this->assertEquals('new for tickets, call (555) 555 555', $concert->additional_information);
        });
    }
}