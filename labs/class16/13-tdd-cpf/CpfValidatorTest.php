<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/13-cpf.php';

class CpfValidatorTest extends TestCase
{
    public function testValidCpf(): void
    {
        $this->assertTrue(validateCpf("529.982.247-25"));
    }

    public function testInvalidCpf(): void
    {
        $this->assertFalse(validateCpf("111.111.111-11"));
    }

    public function testRejectsWrongLength(): void
    {
        $this->assertFalse(validateCpf("123"));
    }

    public function testRejectsLetters(): void
    {
        $this->assertFalse(validateCpf("abc.def.ghi-jk"));
    }
}
