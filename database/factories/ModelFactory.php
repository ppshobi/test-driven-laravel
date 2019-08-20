<?php
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
        'subtitle' => 'Concert Sub Title',
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => 3250,
        'venue' => 'The venue',
        'venue_address' => '123 example lane',
        'city' => 'Laraville',
        'state' => 'ON',
        'zip' => '17926',
        'additional_information' => 'for tickets, call (555) 555 555'
    ];
});
