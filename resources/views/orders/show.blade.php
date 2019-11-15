{{ $order->confirmation_number }}
${{ number_format($order->amount/100, 2) }}
**** **** **** {{ $order->card_last_four }}
@foreach($order->tickets as $ticket)
    {{ $ticket->code }}
@endforeach