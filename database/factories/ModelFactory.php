<?php

use App\User;
use App\Concert;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Concert::class, function (Faker\Generator $faker) {
    return [
        'title' => 'Concert Title',
        'user_id' => function(){
            return factory(User::class)->create()->id;
        },
        'subtitle' => 'Concert Sub Title',
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => 2000,
        'venue' => 'The venue',
        'venue_address' => '123 example lane',
        'city' => 'Laraville',
        'published_at' => Carbon::today(),
        'state' => 'ON',
        'zip' => '17926',
        'additional_information' => 'for tickets, call (555) 555 555'
    ];
});

$factory->state(App\Concert::class, 'unpublished', function ($faker) {
    return [
        'published_at' => null,
    ];
});

$factory->define(App\Ticket::class, function (Faker\Generator $faker) {
    return [
        'concert_id' => function() {
            return factory(Concert::class)->create()->id;
        }
    ];
});

$factory->state(App\Ticket::class, 'reserved', function ($faker) {
    return [
        'reserved_at' => Carbon::now(),
    ];
});

$factory->define(App\Order::class, function (Faker\Generator $faker) {
    return [
        'amount' => 5250,
        'email'  => 'someone@example.com',
        'confirmation_number'  => 'ORDERCONFIRMATION1234',
        'card_last_four'  => '1234',
    ];
});
