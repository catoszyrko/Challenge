<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Customer;

class PosnetController extends Controller
{
    public function registerCard(Request $request)
    {
        $request->validate([
            'dni' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'number' => 'required|digits:8|unique:cards',
            'type' => 'required|in:VISA,AMEX',
            'bank' => 'required|string',
            'limit' => 'required|numeric|min:0',
        ]);

        $customer = Customer::firstOrCreate(
            ['dni' => $request->dni],
            ['first_name' => $request->first_name, 'last_name' => $request->last_name]
        );

        $card = Card::create([
            'number' => $request->number,
            'type' => $request->type,
            'bank' => $request->bank,
            'limit' => $request->limit,
            'customer_id' => $customer->id,
        ]);

        return response()->json(['message' => 'Card registered successfully.', 'card' => $card], 201);
    }

    public function doPayment(Request $request)
    {
        $request->validate([
            'card_number' => 'required|exists:cards,number',
            'amount' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1|max:6',
        ]);

        $card = Card::where('number', $request->card_number)->first();

        $extra_percentage = ($request->installments > 1) ? 0.03 * ($request->installments - 1) : 0;
        $total_amount = $request->amount + ($request->amount * $extra_percentage);

        if ($card->limit < $total_amount) {
            return response()->json(['error' => 'Insufficient card limit'], 400);
        }

        $card->limit -= $total_amount;
        $card->save();

        $ticket = [
            'customer_name' => $card->customer->first_name . ' ' . $card->customer->last_name,
            'total_amount' => $total_amount,
            'installment_amount' => $total_amount / $request->installments,
        ];

        return response()->json(['message' => 'Payment successful.', 'ticket' => $ticket], 200);
    }
}
