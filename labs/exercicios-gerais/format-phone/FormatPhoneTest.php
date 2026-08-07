<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/format-phone.php';

class FormatPhoneTest extends TestCase
{
    public function testTenDigitsFormat()
    {
        $this->assertEquals('(11) 9999-8888', formatPhone('1199998888'));
    }

    public function testElevenDigitsFormat()
    {
        $this->assertEquals('(11) 99999-8888', formatPhone('11999998888'));
    }

    public function testTenDigitsDifferentDDD()
    {
        $this->assertEquals('(21) 1234-5678', formatPhone('2112345678'));
    }

    public function testElevenDigitsDifferentDDD()
    {
        $this->assertEquals('(31) 98765-4321', formatPhone('31987654321'));
    }

    public function testAllZeros()
    {
        $this->assertEquals('(00) 0000-0000', formatPhone('0000000000'));
    }

    public function testAllZerosElevenDigits()
    {
        $this->assertEquals('(00) 00000-0000', formatPhone('00000000000'));
    }

    public function testEmptyStringThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('');
    }

    public function testLessThanTenDigitsThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('119999888');
    }

    public function testNineDigitsThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('123456789');
    }

    public function testMoreThanElevenDigitsThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('119999988880');
    }

    public function testLettersThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('11abcdefghij');
    }

    public function testMixedLettersAndNumbersThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('11abc999988');
    }

    public function testSpecialCharactersThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('11-9999-8888');
    }

    public function testFormattedInputThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('(11) 9999-8888');
    }

    public function testSpacesThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('11 9999 8888');
    }

    public function testSingleDigitThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('0');
    }

    public function testLeadingSpacesThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone(' 1199998888');
    }

    public function testTrailingSpacesThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        formatPhone('1199998888 ');
    }
}
