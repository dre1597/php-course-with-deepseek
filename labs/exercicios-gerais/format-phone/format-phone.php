<?php

function formatPhone(string $phone): string
{
    if (preg_match('/[^0-9]/', $phone)) {
        throw new InvalidArgumentException('Invalid phone number');
    }

    if (strlen($phone) !== 10 && strlen($phone) !== 11) {
        throw new InvalidArgumentException('Invalid phone number');
    }

    $ddd = substr($phone, 0, 2);
    $actualNumber = substr($phone, 2);
    $actualNumberLen = strlen($actualNumber);

    $formattedActualNumber = $actualNumberLen === 8 ?
        substr($actualNumber, 0, 4) . '-' . substr($actualNumber, 4) :
        substr($actualNumber, 0, 5) . '-' . substr($actualNumber, 5);

    return "($ddd) $formattedActualNumber";
}