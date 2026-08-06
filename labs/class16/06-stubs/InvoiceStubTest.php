<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/06-invoice-service.php';

class InvoiceStubTest extends TestCase
{
    public function testSendsInvoiceWithStub(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')
               ->willReturn(true);

        $service = new InvoiceService($mailer);
        $result = $service->sendInvoice('dre@email.com', 199.90);

        $this->assertTrue($result);
    }
}
