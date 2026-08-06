<?php

function generatePassword(int $length = 16): string
{
    if ($length < 4) {
        throw new InvalidArgumentException('Password must have at least 4 characters');
    }

    $firstLetter = generateAUpperCaseLetter();

    $digit = generateADigit();
    $specialCharacter = generateASpecialCharacter();
    $lowerCaseLetter = generateALowerCaseLetter();

    $password = $digit . $specialCharacter . $lowerCaseLetter;

    $randomFunctions = ['generateAUpperCaseLetter', 'generateADigit', 'generateASpecialCharacter', 'generateALowerCaseLetter'];
    for ($i = 0; $i < $length - 4; $i++) {
        $randomCharacter = $randomFunctions[array_rand($randomFunctions)]();
        $password .= $randomCharacter;
    }

    $shuffled = str_shuffle($password);

    return $firstLetter . $shuffled;
}

function generateAUpperCaseLetter(): string
{
    return chr(rand(65, 90));
}

function generateADigit(): string
{
    return chr(rand(48, 57));
}

function generateASpecialCharacter(): string
{
    return chr(rand(33, 47));
}

function generateALowerCaseLetter(): string
{
    return chr(rand(97, 122));
}
