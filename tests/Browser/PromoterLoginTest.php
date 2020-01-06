<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use http\Client\Curl\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;
    /**
     * @test
     */
    public function loggin_in_successfully()
    {
        $user = factory(\App\User::class)->create([
            'email' => 'jain@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'jain@example.com')
                ->type('password', 'super-secret-password')
                ->press('Log in')
                ->assertPathIs('/backstage/concerts');


        });
    }

    /**
     * @test
     */
    public function loggin_in_with_invalid_credentials()
    {
        $user = factory(\App\User::class)->create([
            'email' => 'jain@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'jain@example.com')
                ->type('password', 'wrong-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('Credentials Donot match');
        });
    }
}
