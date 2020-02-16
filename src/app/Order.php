<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Order extends Model
{
    protected $guarded = [];

    public static function forTickets(Collection $tickets, string $email, int $amount): Order
    {
        $order = self::create(
            [
                'email' => $email,
                'amount' => $amount,
            ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;

    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }


    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount
        ];
    }

    public static function findByConfirmationNumber(string $confirmationNumber): Order
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }

}