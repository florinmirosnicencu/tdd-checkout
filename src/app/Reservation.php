<?php


namespace App;


use Illuminate\Support\Collection;

class Reservation
{
    /** @var Collection */
    private Collection $tickets;

    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    public function totalCost(): int
    {
        return $this->tickets->sum('price');
    }
}