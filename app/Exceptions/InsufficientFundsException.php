<?php

namespace App\Exceptions;

use Exception;

class InsufficientFundsException extends Exception
{
    // Puedes agregar un mensaje personalizado si lo deseas
    protected $message = 'Insufficient funds to complete the transaction.';

    // También puedes añadir un código de error, si es necesario
    protected $code = 400;  // Código HTTP 400 para Bad Request, por ejemplo

    // Método opcional para personalizar la lógica
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        // Si no se proporciona un mensaje personalizado, usa el predeterminado
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $code, $previous);
    }
}
