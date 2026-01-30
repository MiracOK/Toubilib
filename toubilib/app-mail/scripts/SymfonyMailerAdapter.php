<?php
declare(strict_types=1);

namespace Toubilib\Scripts;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

interface MailerInterface
{
    public function send(string $to, string $subject, string $htmlBody, string $textBody = '', ?string $from = null): void;
}

class SymfonyMailerAdapter implements MailerInterface
{
    private Mailer $mailer;
    private string $defaultFrom = 'no-reply@toubilib.local';

    public function __construct(string $dsn)
    {
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = '', ?string $from = null): void
    {
        $fromAddress = $from ?? $this->defaultFrom;

        $email = (new Email())
            ->from($fromAddress)
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);

        if ($textBody !== '') {
            $email->text($textBody);
        }

        $this->mailer->send($email);
    }
}