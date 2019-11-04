<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotEnoughTicketsException;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates   = ['date', 'published_at'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function orderTickets($email, $ticketCount)
    {
        $tickets = $this->findTickets($ticketCount);

        return $this->createOrder($email, $tickets);
    }

    public function reserveTickets($quantity, $email)
    {
        $tickets = $this->findTickets($quantity)->each(function($ticket){
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException();
        }

        return $tickets;
    }

    public function createOrder($email, $tickets)
    {
        return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');

    }

    public function hasOrderFor($email)
    {
        return $this->orders()
            ->where('email', $email)
            ->count() > 0;
    }

    public function ordersFor($email)
    {
        return $this->orders()
            ->where('email', $email)
            ->get();
    }

    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }
}
