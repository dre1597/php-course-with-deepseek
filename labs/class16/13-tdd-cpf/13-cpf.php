<?php

declare(strict_types=1);

function validateCpf(string $cpf): bool
{
    $cpf = preg_replace("/\\D/", "", $cpf);

    if (strlen($cpf) !== 11) {
        return false;
    }

    if (!is_numeric($cpf)) {
        return false;
    }

    if (preg_match("/^(\\d)\\1{10}$/", $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $sum = 0;
        for ($i = 0; $i < $t; $i++) {
            $sum += (int) $cpf[$i] * (($t + 1) - $i);
        }
        $digit = ($sum * 10) % 11 % 10;
        if ((int) $cpf[$t] !== $digit) {
            return false;
        }
    }

    return true;
}
