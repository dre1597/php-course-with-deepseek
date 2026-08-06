<?php

declare(strict_types=1);

interface MailerInterface
{
    public function send(string $to, string $subject, string $body): bool;
}

class InvoiceService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendInvoice(string $to, float $amount): bool
    {
        $body = "Your invoice: amount: " . number_format($amount, 2);
        return $this->mailer->send($to, 'Your invoice', $body);
    }
}
