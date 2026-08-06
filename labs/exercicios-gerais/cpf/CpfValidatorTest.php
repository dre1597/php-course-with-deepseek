<?php

use PHPUnit\Framework\TestCase;

require_once 'validate_cpf.php';

class CpfValidatorTest extends TestCase
{
    public function testValidCpf(): void
    {
        $this->assertTrue(validateCPF('529.982.247-25'));
    }

    public function testInvalidCpf(): void
    {
        $this->assertFalse(validateCPF('111.111.111-11'));
    }

    public function testRejectsWrongLength(): void
    {
        $this->assertFalse(validateCPF('123'));
    }

    public function testRejectsLetters(): void
    {
        $this->assertFalse(validateCPF('abc.def.ghi-jk'));
    }

    public function testValidCpfOnlyDigits(): void
    {
        $this->assertTrue(validateCPF('52998224725'));
    }

    public function testAnotherValidCpf(): void
    {
        $this->assertTrue(validateCPF('123.456.789-09'));
    }

    public function testAnotherValidCpfOnlyDigits(): void
    {
        $this->assertTrue(validateCPF('12345678909'));
    }

    public function testFirstCheckDigitWrong(): void
    {
        $this->assertFalse(validateCPF('529.982.247-35'));
    }

    public function testSecondCheckDigitWrong(): void
    {
        $this->assertFalse(validateCPF('529.982.247-26'));
    }

    public function testBothCheckDigitsWrong(): void
    {
        $this->assertFalse(validateCPF('529.982.247-00'));
    }

    public function testEmptyString(): void
    {
        $this->assertFalse(validateCPF(''));
    }

    public function testOnlySpecialCharacters(): void
    {
        $this->assertFalse(validateCPF('...'));
    }

    public function testOnlySpaces(): void
    {
        $this->assertFalse(validateCPF('   '));
    }

    public function testCpfWithAllZeros(): void
    {
        $this->assertFalse(validateCPF('000.000.000-00'));
    }

    public function testCpfWithAllNines(): void
    {
        $this->assertFalse(validateCPF('999.999.999-99'));
    }

    public function testCpfWithElevenSameNonRepeatingDigits(): void
    {
        $this->assertFalse(validateCPF('777.777.777-77'));
    }

    public function testCpfWithElevenDigitsInvalid(): void
    {
        $this->assertFalse(validateCPF('12345678901'));
    }

    public function testCpfWithPartialFormatting(): void
    {
        $this->assertTrue(validateCPF('529.982247-25'));
    }

    public function testCpfWithExtraSpaces(): void
    {
        $this->assertTrue(validateCPF('  529.982.247-25  '));
    }

    public function testCpfTooLong(): void
    {
        $this->assertFalse(validateCPF('123.456.789-091'));
    }

    public function testCpfOneDigitShort(): void
    {
        $this->assertFalse(validateCPF('529.982.247-2'));
    }

    public function testCpfExactlyElevenDigitsButWrong(): void
    {
        $this->assertFalse(validateCPF('52998224720'));
    }
}
