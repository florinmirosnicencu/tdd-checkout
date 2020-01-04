<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];

    protected $dates = ['date'];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets(string $email, int $ticketQuantity)
    {
        $tickets = $this->findTickets($ticketQuantity);
        return $this->createOrder($email, $tickets);
    }

    public function addTickets(int $quantity)
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

    public function hasOrderFor(string $customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->count() > 0;
    }

    public function ordersFor(string $customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->get();
    }

    /**
     * @param int $quantity
     * @return mixed
     */
    public function findTickets(int $quantity)
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() !== $quantity) {
            throw new NotEnoughTicketsException;
        }
        return $tickets;
    }

    /**
     * @param string $email
     * @param $tickets
     * @return Model
     */
    public function createOrder(string $email, $tickets): Model
    {
        $order = $this->orders()->create(
            [
                'email' => $email,
                'amount' => $tickets->count() * $this->ticket_price,
            ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }
}
