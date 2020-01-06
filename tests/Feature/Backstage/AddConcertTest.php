<?php

namespace Tests\Feature\Backstage;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

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
}