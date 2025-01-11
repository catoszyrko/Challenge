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
            'number' => 'required|digits:8|unique:cards',
            'type' => 'required|in:VISA,AMEX',
            'bank' => 'required|string',
            'limit' => 'required|numeric|min:0',
            'customer_id' => 'required',
        ]);

        $customer = Customer::firstOrCreate(
            ['id' => $request->customer_id]
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
            'card_id' => 'required|exists:cards,id',
            'amount' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1|max:6',
        ]);

        $card = Card::where('id', $request->card_id)->first();

        $extra_percentage = ($request->installments > 1) ? 0.03 * ($request->installments - 1) : 0;
        $total_amount = $request->amount + ($request->amount * $extra_percentage);

        if ($card->limit < $total_amount) {
            return response()->json(['error' => 'Insufficient card limit'], 400);
        }

        $card->limit -= $total_amount;
        $card->save();

        $ticket = [
            'customer_name' => $card->customer->getFullName(),
            'total_amount' => $total_amount,
            'installment_amount' => $total_amount / $request->installments,
        ];

        return response()->json(['message' => 'Payment successful.', 'ticket' => $ticket], 200);
    }
}
