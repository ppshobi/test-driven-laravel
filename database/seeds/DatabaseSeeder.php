<?php

use App\Concert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Concert::class)->create([
            'title' => 'The red chord',
            'subtitle' => 'with animosity & lethargy',
            'venue' => 'The mosh pit',
            'venue_address' => '123 example lane',
            'city' => 'laraville',
            'state' => 'ON',
            'zip' => '673565',
            'date' => Carbon::parse('2016-12-13 8:00pm'),
            'ticket_price' => 3250,
            'additional_information' => 'this concert is 19+',
        ])->addTickets(10);
    }
}
