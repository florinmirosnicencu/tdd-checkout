<?php


namespace App\Http\Controllers;


use App\Billing\PaymentGateway;
use App\Concert;

class ConcertOrdersController extends Controller
{

    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::findOrFail($concertId);

        $ticketQuantity = request('ticket_quantity');
        $amount         = $ticketQuantity * $concert->ticket_price;

        $token = request('payment_token');
        $this->paymentGateway->charge($amount, $token);
        return response()->json([], 201);
    }
}