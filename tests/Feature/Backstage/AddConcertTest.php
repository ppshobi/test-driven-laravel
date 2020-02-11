<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    private function from($url)
    {
        session()->setPreviousUrl(url($url));
        return $this;
    }

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
    function promoters_can_view_the_add_concerts_form()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    /**
     * @test
     **/
    function guests_can_not_view_the_add_concerts_form()
    {

        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     **/
    function adding_a_valid_concert()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts/',[
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
        ]);

        tap(Concert::first(), function ($concert) use ($response, $user){
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertTrue($concert->user->is($user));

            $this->assertEquals("The red chord", $concert->title);
            $this->assertEquals("with animosity & lethargy", $concert->subtitle);
            $this->assertEquals("this concert is 19+", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals("The mosh pit", $concert->venue);
            $this->assertEquals("123 example lane", $concert->venue_address);
            $this->assertEquals("laraville", $concert->city);
            $this->assertEquals("ON", $concert->state);
            $this->assertEquals("673565", $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticketsRemaining());
        });
    }

    /**
     * @test
     **/
    function guests_cant_add_new_concerts()
    {
        $response = $this->post('/backstage/concerts/',$this->validParams());

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }

    /**
     * @test
     **/
    function title_is_required()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts/',$this->validParams([
                'title' => '',
            ]));

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors(['title']);
        $this->assertEquals(0, Concert::count());
    }

      /**
     * @test
     **/
    function subtitle_is_optional()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts/',$this->validParams([
            'subtitle' => '',
        ]));

        tap(Concert::first(), function ($concert) use ($response, $user){
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertTrue($concert->user->is($user));
            $this->assertNull($concert->subtitle);
        });
    }

    /**
     * @test
     */
    function additional_information_is_optional()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts/',$this->validParams([
            'additional_information' => '',
        ]));

        tap(Concert::first(), function ($concert) use ($response, $user){
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertTrue($concert->user->is($user));

            $this->assertNull($concert->additional_information);
        });
    }
}