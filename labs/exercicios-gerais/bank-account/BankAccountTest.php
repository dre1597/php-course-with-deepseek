<?php

namespace Models\BankAccount;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once __DIR__ . '/BankAccount.php';

class BankAccountTest extends TestCase
{
    public function testAccountIsCreatedWithCorrectInitialBalance(): void
    {
        $account = new BankAccount('João', '12345-6', 500.00);

        $this->assertSame(500.00, $account->getBalance());
    }

    public function testNewAccountWithoutInitialBalanceDefaultsToZero(): void
    {
        $account = new BankAccount('Maria', '67890-1');

        $this->assertSame(0.0, $account->getBalance());
    }

    public function testDepositIncreasesBalance(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $account->deposit(50.00);

        $this->assertSame(150.00, $account->getBalance());
    }

    public function testDepositOfZeroIsAllowed(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $account->deposit(0.00);

        $this->assertSame(100.00, $account->getBalance());
    }

    public function testDepositOfNegativeValueThrowsException(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Deposit amount cannot be negative.');

        $account->deposit(-50.00);
    }

    public function testDepositOfNegativeValueDoesNotChangeBalance(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        try {
            $account->deposit(-50.00);
        } catch (InvalidArgumentException) {
        }

        $this->assertSame(100.00, $account->getBalance());
    }

    public function testWithdrawDecreasesBalance(): void
    {
        $account = new BankAccount('João', '12345-6', 200.00);

        $account->withdraw(80.00);

        $this->assertSame(120.00, $account->getBalance());
    }

    public function testWithdrawFullBalanceZerosTheAccount(): void
    {
        $account = new BankAccount('João', '12345-6', 75.50);

        $account->withdraw(75.50);

        $this->assertSame(0.0, $account->getBalance());
    }

    public function testWithdrawOfNegativeValueThrowsException(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Withdraw amount cannot be negative.');

        $account->withdraw(-20.00);
    }

    public function testWithdrawOfNegativeValueDoesNotChangeBalance(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        try {
            $account->withdraw(-20.00);
        } catch (InvalidArgumentException) {
        }

        $this->assertSame(100.00, $account->getBalance());
    }

    public function testWithdrawOfZeroIsAllowed(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $account->withdraw(0.00);

        $this->assertSame(100.00, $account->getBalance());
    }

    public function testWithdrawMoreThanBalanceThrowsException(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Insufficient balance.');

        $account->withdraw(150.00);
    }

    public function testWithdrawMoreThanBalanceDoesNotChangeBalance(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        try {
            $account->withdraw(150.00);
        } catch (RuntimeException) {
        }

        $this->assertSame(100.00, $account->getBalance());
    }

    public function testMultipleDepositsAccumulateCorrectly(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $account->deposit(50.00);
        $account->deposit(25.00);
        $account->deposit(10.00);

        $this->assertSame(185.00, $account->getBalance());
    }

    public function testMultipleWithdrawsDecreaseCorrectly(): void
    {
        $account = new BankAccount('João', '12345-6', 200.00);

        $account->withdraw(30.00);
        $account->withdraw(45.00);
        $account->withdraw(60.00);

        $this->assertSame(65.00, $account->getBalance());
    }

    public function testSequenceOfDepositsAndWithdrawsKeepsCorrectBalance(): void
    {
        $account = new BankAccount('João', '12345-6', 500.00);

        $account->deposit(200.00);
        $account->withdraw(150.00);
        $account->deposit(50.00);
        $account->withdraw(400.00);

        $this->assertSame(200.00, $account->getBalance());
    }

    public function testWithdrawThatExceedsBalanceMidSequenceKeepsPreviousState(): void
    {
        $account = new BankAccount('João', '12345-6', 500.00);

        $account->withdraw(100.00);
        $account->deposit(50.00);

        try {
            $account->withdraw(500.00);
        } catch (RuntimeException) {
        }

        $this->assertSame(450.00, $account->getBalance());
    }

    public function testDepositWithDecimalValuesWorks(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $account->deposit(33.33);
        $account->deposit(66.67);

        $this->assertSame(200.00, $account->getBalance());
    }

    public function testWithdrawWithDecimalValuesWorks(): void
    {
        $account = new BankAccount('João', '12345-6', 99.99);

        $account->withdraw(33.33);

        $this->assertSame(66.66, $account->getBalance());
    }

    public function testOwnerPropertyIsSetCorrectly(): void
    {
        $account = new BankAccount('Alice', '00001-0', 250.00);

        $this->assertSame('Alice', $account->owner);
    }

    public function testAccountNumberPropertyIsSetCorrectly(): void
    {
        $account = new BankAccount('Bob', '98765-4', 1000.00);

        $this->assertSame('98765-4', $account->accountNumber);
    }

    public function testNegativeInitialBalanceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Initial balance cannot be negative.');

        new BankAccount('Invalid', '00000-0', -100.00);
    }

    public function testEmptyOwnerNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Owner name cannot be empty.');

        new BankAccount('', '12345-6', 100.00);
    }

    public function testEmptyAccountNumberThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Account number cannot be empty.');

        new BankAccount('Alice', '', 100.00);
    }

    public function testWithdrawLeavingOnlyOneCent(): void
    {
        $account = new BankAccount('João', '12345-6', 100.00);

        $account->withdraw(99.99);

        $this->assertEqualsWithDelta(0.01, $account->getBalance(), 0.001);
    }

    public function testVeryLargeDeposit(): void
    {
        $account = new BankAccount('João', '12345-6', 0.00);

        $account->deposit(999999.99);

        $this->assertSame(999999.99, $account->getBalance());
    }

    public function testMultipleAccountsAreIndependent(): void
    {
        $alice = new BankAccount('Alice', '11111-1', 500.00);
        $bob = new BankAccount('Bob', '22222-2', 300.00);

        $alice->deposit(100.00);
        $bob->withdraw(50.00);

        $this->assertSame(600.00, $alice->getBalance());
        $this->assertSame(250.00, $bob->getBalance());
    }

    public function testDepositAndWithdrawSameValueRoundtrip(): void
    {
        $account = new BankAccount('João', '12345-6', 500.00);

        $account->deposit(200.00);
        $account->withdraw(200.00);

        $this->assertSame(500.00, $account->getBalance());
    }

    public function testWithdrawBarelyMoreThanBalanceThrowsException(): void
    {
        $account = new BankAccount('João', '12345-6', 50.00);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Insufficient balance.');

        $account->withdraw(50.01);
    }
}
