<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mockery;

require_once __DIR__ . '/08-mailer.php';

class InvoiceSpyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSpyRecordsThatMailerWasCalled(): void
    {
        $spy = Mockery::spy(Mailer::class);
        $spy->allows()->send(Mockery::any(), Mockery::any(), Mockery::any())
                      ->andReturn(true);

        $service = new InvoiceService($spy);
        $result = $service->sendInvoice('dre@email.com', 199.90);

        $this->assertTrue($result);

        $spy->shouldHaveReceived('send')
            ->once()
            ->with('dre@email.com', 'Your invoice', Mockery::pattern('/amount: 199\.\d{2}/'));
    }
}
