<?php

namespace Tests;

use Tests\TestCase;
use App\Models\Card;
use App\Models\Posnet;
use App\Exceptions\PaymentException;
use App\Models\Customer;

class PosnetTest extends TestCase
{
    public function testSuccessfulPayment()
    {
        $posnet = new Posnet();

        $customer = new Customer([
            'dni' => '12345678',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $customer->save();

        $card = $posnet->registerCard("12345678", "AMEX", "Bank A", 1000, $customer);
        $card->save();
        
        $result = $posnet->doPayment($card, 200, 3);
        
        $this->assertEquals("John Doe", $result['customer_name']);
        $this->assertEquals(212.0, $result['total_amount']); // 200 + 3% per installment
        $this->assertEquals(70.67, round($result['installment_amount'], 2));
    }

    public function testSuccessfulPaymentApi()
    {
        // Crear un cliente
        $customerData = [
            'dni' => '12345678',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $customer = new Customer($customerData);
        $customer->save();
        
        // Obtener el cliente creado (esto es solo un ejemplo, asume que la respuesta contiene el id)
        $customer = Customer::first();

        // Registramos la tarjeta (simulando con los datos de tarjeta)
        $cardData = [
            'number' => '12345678',
            'type' => 'AMEX',
            'bank' => 'Bank A',
            'limit' => 1000,
            'customer_id' => $customer->id,
        ];
        
        $response = $this->postJson('/api/cards', $cardData);
        $response->assertStatus(201);
        
        $card = Card::first();
        
        // Ahora realizamos el pago (se envían los datos de la tarjeta, monto y cuotas)
        $paymentData = [
            'card_id' => $card->id,
            'amount' => 200,
            'installments' => 3,
        ];

        $response = $this->postJson('/api/payments', $paymentData);
        
        // Verificar los resultados de la respuesta
        $response->assertStatus(200);
        $result = $response->json();
        
        $this->assertEquals("John Doe", $result['ticket']['customer_name']);
        $this->assertEquals(212.0, $result['ticket']['total_amount']); // 200 + 3% per installment
        $this->assertEquals(70.67, round($result['ticket']['installment_amount'], 2));
    }

    public function testInsufficientLimit()
    {
        $this->expectException(PaymentException::class);

        $posnet = new Posnet();

        $customer = new Customer([
            'dni' => '12345678',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        $customer->save();
        
        $card = $posnet->registerCard("12345678", "AMEX", "Bank B", 100, $customer);
        $card->save();

        $posnet->doPayment($card, 200, 2);
    }
}
