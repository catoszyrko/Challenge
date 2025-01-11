<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('number', 8); // Número de tarjeta, debe ser de 8 dígitos
            $table->enum('type', ['Visa', 'AMEX']); // Tipo de tarjeta
            $table->string('bank'); // Banco de la tarjeta
            $table->float('limit'); // Límite disponible
            $table->foreignId('customer_id')->constrained(); // Relación con el cliente
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};

