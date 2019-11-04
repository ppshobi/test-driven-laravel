<?php

namespace App\Http\Controllers;

use App\Order;
use App\Reservation;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);
        $this->validate(request(), [
            'email'           => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'purchase_token'   => ['required'],
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'),  request('email'));

            $this->paymentGateway->charge($reservation->totalCost(), request('purchase_token'));
            $order = Order::forTickets($reservation->tickets(), $reservation->email(), $reservation->totalCost());
            return response()->json($order, 201);
        }catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([], 422);
        }catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }

    }
}
