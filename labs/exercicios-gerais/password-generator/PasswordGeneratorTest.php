<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/password_generator.php';

class PasswordGeneratorTest extends TestCase
{
    public function testGeneratePassword(): void
    {
        $this->assertIsString(generatePassword());
    }

    public function testGeneratePasswordWithLength(): void
    {
        $expectedLength = random_int(4, 20);
        $resultedLength = strlen(generatePassword($expectedLength));
        $this->assertSame($expectedLength, $resultedLength);
    }

    public function testDoNotGeneratePasswordWithLessThan4Characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        generatePassword(3);
    }

    public function testGeneratePasswordWithAUpperCaseLetter(): void
    {
        $password = generatePassword();
        $this->assertMatchesRegularExpression('/[A-Z]/', $password);
    }

    public function testGeneratePasswordWithALowerCaseLetter(): void
    {
        $password = generatePassword();
        $this->assertMatchesRegularExpression('/[a-z]/', $password);
    }

    public function testGeneratePasswordWithADigit(): void
    {
        $password = generatePassword();
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
    }

    public function testGeneratePasswordWithSpecialCharacter(): void
    {
        $password = generatePassword();
        $this->assertMatchesRegularExpression('/[^A-Za-z0-9]/', $password);
    }

    public function testPasswordStartsWithUpperCaseLetter(): void
    {
        $password = generatePassword();
        $this->assertMatchesRegularExpression('/^[A-Z]/', $password);
    }
}
