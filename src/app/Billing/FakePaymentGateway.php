<?php


namespace App\Billing;


use Closure;

class FakePaymentGateway implements PaymentGateway
{
    private $charges;
    /**
     * @var ?\Closure
     */
    private ?Closure $beforeFirstChargeCallback = null;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken(): string
    {
        return 'valid-token';
    }

    public function charge(int $amount, string $token): void
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException;
        }

        $this->charges[] = $amount;
    }

    public function getTotalCharges(): int
    {
        return $this->charges->sum();
    }

    public function beforeFirstCharge(Closure $callback): void
    {
        $this->beforeFirstChargeCallback = $callback;
    }

}