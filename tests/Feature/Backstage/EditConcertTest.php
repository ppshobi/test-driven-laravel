<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;
    protected function setUp()
    {
        parent::setUp();

        TestResponse::macro('data', function ($key){
            return $this->original->getData()[$key];
        });
    }

    private function oldAttributes($overrides = [])
    {
        return array_merge([
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
            'ticket_quantity'        => 5,
        ], $overrides);
    }

    private function validParams($overRides = [])
    {
        return array_merge([
           'title'                  => 'New Concert Title',
            'subtitle'               => 'New Concert Sub Title',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'ticket_price'           => 2000,
            'ticket_quantity'        => 10,
            'venue'                  => 'New The venue',
            'venue_address'          => 'New 123 example lane',
            'city'                   => 'New Laraville',
            'state'                  => 'New ON',
            'zip'                    => '123456',
            'additional_information' => 'New for tickets, call (111) 111 111',
//            'published_at'           => null,
        ], $overRides);
    }

    /**
     * @test
     **/
    function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);
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
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

     /**
     * @test
     **/
    function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
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
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_non_existent_concert()
    {
        $user     = factory(User::class)->create();

        $response = $this->get("/backstage/concerts/999/edit");

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
        $concert = factory(Concert::class)->states('unpublished')->create($this->oldAttributes([
            'user_id'                =>  $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertRedirect('/backstage/concerts');
    }

    /**
     * @test
     **/
    function promoters_cannot_edit_other_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create(
            $this->oldAttributes(['user_id' =>  $otherUser->id])
        );

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(404);

        $this->assertArraySubset(
            $this->oldAttributes(['user_id' => $otherUser->id]),
            $concert->fresh()->getAttributes()
        );
    }

    /**
     * @test
     **/
    function promoters_cannot_edit_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create($this->oldAttributes([
            'user_id'                =>  $user->id,
        ]));

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(403);
        $this->assertArraySubset(
            $this->oldAttributes(['user_id' => $user->id]),
            $concert->fresh()->getAttributes()
        );
    }
    /**
     * @test
     **/
    function guests_cannot_edit_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create($this->oldAttributes([
            'user_id'                =>  $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertRedirect('/login');

        $this->assertArraySubset(
            $this->oldAttributes(['user_id' => $user->id]),
            $concert->fresh()->getAttributes()
        );
    }

    /**
     * @test
     **/
    function title_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create($this->oldAttributes(['user_id' => $user->id]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}",$this->validParams([
                'title' => '',
            ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors(['title']);

        $this->assertArraySubset(
            $this->oldAttributes(['user_id' => $user->id]),
            $concert->fresh()->getAttributes()
        );
    }


}