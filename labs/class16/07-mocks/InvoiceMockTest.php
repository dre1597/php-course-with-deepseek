<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/07-invoice-service.php';

class InvoiceMockTest extends TestCase
{
    public function testMockPreventsRealMailerFromRunning(): void
    {
        $mailer = $this->createMock(Mailer::class);

        $mailer->expects($this->once())
               ->method('send')
               ->with('dre@email.com', 'Your invoice', $this->stringContains('amount: 199.90'))
               ->willReturn(true);

        $service = new InvoiceService($mailer);
        $service->sendInvoice('dre@email.com', 199.90);
    }
}
