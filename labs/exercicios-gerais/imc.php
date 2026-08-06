<?php

$weight = $argv[1] ?? 90;
$height = $argv[2] ?? 1.65;

function getUserIMC($weight, $height)
{
    return $weight / ($height ** 2);
}

$imc = round(getUserIMC($weight, $height), 1);

$result = match (true) {
    $imc < 18.5 => 'Underweight',
    $imc >= 18.5 && $imc < 25 => 'Normal weight',
    $imc >= 25 && $imc < 30 => 'Overweight',
    default => 'Obesity',
};

echo "IMC: $imc. Result: $result\n";
