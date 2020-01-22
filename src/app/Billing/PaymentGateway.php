<?php

namespace App\Billing;

interface PaymentGateway
{
    public function charge(int $amount, string $token): void;

    public function getValidTestToken(): String;

    public function newChargesDuring($callback): object;
}