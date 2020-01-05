<?php


namespace App;


use Illuminate\Support\Collection;

class Reservation
{
    /**
     * @var Collection
     */
    private Collection $tickets;
    /**
     * @var string
     */
    public string $email;

    public function __construct(Collection $tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost(): int
    {
        return $this->tickets->sum('price');
    }

    public function cancel(): void
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }

    public function tickets(): Collection
    {
        return $this->tickets;
    }

    public function email(): string
    {
        return $this->email();
    }
}