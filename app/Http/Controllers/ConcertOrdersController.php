<?php

namespace App\Http\Controllers;

use App\Concert;
use App\Billing\PaymentGateway;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationEmail;
use App\Billing\PaymentFailedException;
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
            'purchase_token'  => ['required'],
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'),  request('email'));
            $order = $reservation->complete($this->paymentGateway, request('purchase_token'));
            Mail::to($order->email)->send(new OrderConfirmationEmail($order));
            return response()->json($order, 201);
        }catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([], 422);
        }catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
