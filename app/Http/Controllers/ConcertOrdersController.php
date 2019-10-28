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
            $tickets = $concert->reserveTickets(request('ticket_quantity'));

            $reservation = new Reservation($tickets);

            $this->paymentGateway->charge($reservation->totalCost(), request('purchase_token'));
//            $order = $concert->createOrder(request('email'), $tickets);
            $order = Order::forTickets($tickets, request('email'), $reservation->totalCost());
            return response()->json($order, 201);
        }catch (PaymentFailedException $e) {
            return response()->json([], 422);
        }catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }

    }
}
