<?php

namespace Tests\Feature\Backstage;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * @test
     **/
    function login_with_valid_credentials()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
             'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/backstage/concerts/new');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

     /**
     * @test
     **/
    function login_with_invalid_credentials()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
             'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(Auth::check());
    }
     /**
     * @test
     **/
    function login_with_account_does_not_exists()
    {
        $this->disableExceptionHandling();

        $response = $this->post('/login', [
             'email' => 'none@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }

    /**
     * @test
     **/
    function loggin_out_the_current_user()
    {
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}