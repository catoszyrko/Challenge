<?php

namespace App;

class Customer
{
    private string $dni;
    private string $firstName;
    private string $lastName;

    public function __construct(string $dni, string $firstName, string $lastName)
    {
        $this->dni = $dni;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
