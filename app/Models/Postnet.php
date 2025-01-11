<?php

namespace App;

class Posnet
{
    public function registerCard(string $number, string $type, string $bank, float $limit, Customer $customer): Card
    {
        return new Card($number, $type, $bank, $limit, $customer);
    }

    public function doPayment(Card $card, float $amount, int $installments): array
    {
        if ($installments < 1 || $installments > 6) {
            throw new \App\Exceptions\PaymentException("Installments must be between 1 and 6.");
        }

        $totalAmount = $amount;
        if ($installments > 1) {
            $totalAmount += $amount * 0.03 * ($installments - 1);
        }

        if (!$card->hasSufficientLimit($totalAmount)) {
            throw new \App\Exceptions\PaymentException("Insufficient limit for this payment.");
        }

        $card->reduceLimit($totalAmount);
        $customer = $card->getCustomer();

        return [
            'customer_name' => $customer->getFullName(),
            'total_amount' => $totalAmount,
            'installment_amount' => $totalAmount / $installments,
        ];
    }
}
