<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
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
            'user_id' => $user->id,
            ''
        ]);


    }
}