<?php

declare(strict_types=1);

class Mailer
{
    public function send(string $to, string $subject, string $body): bool
    {
        echo "[SENT] To: {$to} | {$subject}: {$body}\n";
        return true;
    }
}

class InvoiceService
{
    public function __construct(private Mailer $mailer) {}

    public function sendInvoice(string $to, float $amount): bool
    {
        $body = "Your invoice: amount: " . number_format($amount, 2);
        return $this->mailer->send($to, 'Your invoice', $body);
    }
}
