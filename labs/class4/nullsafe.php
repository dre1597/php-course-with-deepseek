<?php

class Address
{
    public function __construct(
        public string $street,
        public ?City $city = null,
    ) {}
}

class City
{
    public function __construct(
        public string $name,
        public ?State $state = null,
    ) {}
}

class State
{
    public function __construct(
        public string $code,
    ) {}
}

$address = new Address(
    'Flower Street',
    new City('São Paulo', new State('SP'))
);

// Manual null check
$code = null;
if ($address->city !== null && $address->city->state !== null) {
    $code = $address->city->state->code;
}

// Nullsafe
$code = $address->city?->state?->code;
echo $code; // SP

$addressWithoutCity = new Address('Central Ave', null);
$code = $addressWithoutCity->city?->state?->code;
var_dump($code); // NULL — no error thrown