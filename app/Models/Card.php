<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InvalidCardException;
use App\Exceptions\InsufficientFundsException;

class Card extends Model
{
    // Definir la tabla que usará este modelo (si no se usa la convención de plural)
    protected $table = 'cards'; 

    // Definir los atributos que pueden ser asignados masivamente
    protected $fillable = ['number', 'type', 'bank', 'limit', 'customer_id'];

    // Relación con el modelo Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Método para verificar si hay suficiente límite disponible
    public function hasSufficientLimit(float $amount)
    {
        return $this->limit >= $amount;
    }

    // Método para reducir el límite después de un pago
    public function reduceLimit(float $amount)
    {
        if ($this->hasSufficientLimit($amount)) {
            $this->limit -= $amount;
            $this->save();
        } else {
            throw new InsufficientFundsException("Insufficient funds on the card.");
        }
    }

    // Método para procesar un pago
    public function doPayment(float $amount, int $installments)
    {
        // Validar cantidad de cuotas (1-6)
        if ($installments < 1 || $installments > 6) {
            throw new \InvalidArgumentException("Installments must be between 1 and 6.");
        }

        // Calcular recargo si las cuotas son mayores a 1
        $surcharge = 0;
        if ($installments > 1) {
            $surcharge = 0.03 * ($installments - 1); // 3% por cada cuota adicional
        }

        $totalAmount = $amount * (1 + $surcharge);

        // Verificar si hay suficiente límite
        if (!$this->hasSufficientLimit($totalAmount)) {
            throw new InsufficientFundsException("Insufficient funds for this transaction.");
        }

        // Reducir el límite de la tarjeta
        $this->reduceLimit($totalAmount);

        // Lógica para crear el ticket (solo retornarlo sin mostrar)
        $ticket = [
            'customer_name' => $this->customer->getFullName(),
            'total_amount' => $totalAmount,
            'installment_amount' => $totalAmount / $installments,
            'installments' => $installments,
        ];

        // Retornar el ticket generado (no mostrar en la respuesta directamente)
        return $ticket;
    }
}
