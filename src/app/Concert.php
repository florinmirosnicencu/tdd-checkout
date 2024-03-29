<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Concert extends Model
{
    use HasFactory;
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
        return $this->belongsToMany(Order::class, 'tickets');
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

    public function reserveTickets(int $quantity, string $email): Reservation
    {
        $tickets = $this->findTickets($quantity)->each(function ($ticket) {
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    /**
     * @param string $email
     * @param Collection $tickets
     * @return Order
     */
    public function createOrder(string $email, Collection $tickets): Order
    {
        return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }
}
