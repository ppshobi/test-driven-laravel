<?php


namespace App\Http\Controllers\Backstage;


use App\Concert;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => Concert::all()]);
    }

    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store()
    {
        $this->validate(request(), [
            'title' => ['required']
        ]);

        $concert = Auth::user()->concerts()->create([
            'title'                  => request()->title,
            'subtitle'               => request()->subtitle,
            'additional_information' => request()->additional_information,
            'venue'                  => request()->venue,
            'venue_address'          => request()->venue_address,
            'city'                   => request()->city,
            'state'                  => request()->state,
            'zip'                    => request()->zip,
            'date'                   => Carbon::parse(vsprintf("%s %s", [request()->date, request()->time])),
            'ticket_price'           => request()->ticket_price*100,
        ])->addTickets(request()->ticket_quantity);

        $concert->publish();

        return redirect()->route('concerts.show',$concert);
    }
}