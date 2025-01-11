<?php

namespace App;

class Card
{
    public const VALID_TYPES = ['VISA', 'AMEX'];

    private string $number;
    private string $type;
    private string $bank;
    private float $limit;
    private Customer $customer;

    public function __construct(string $number, string $type, string $bank, float $limit, Customer $customer)
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new \App\Exceptions\InvalidCardException("Card type $type is not supported.");
        }

        if (!preg_match('/^\d{8}$/', $number)) {
            throw new \App\Exceptions\InvalidCardException("Invalid card number format.");
        }

        $this->number = $number;
        $this->type = $type;
        $this->bank = $bank;
        $this->limit = $limit;
        $this->customer = $customer;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function hasSufficientLimit(float $amount): bool
    {
        return $this->limit >= $amount;
    }

    public function reduceLimit(float $amount): void
    {
        if (!$this->hasSufficientLimit($amount)) {
            throw new \App\Exceptions\PaymentException("Insufficient limit for payment.");
        }
        $this->limit -= $amount;
    }
}
