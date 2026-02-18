<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

readonly class MailerService
{
    public function __construct(
        #[Autowire(env: 'APP_ADMIN_EMAIL')] private string $adminMail,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function sendNewDemoCreated(): void
    {
        $email = (new Email())
            ->from(new Address('contact@itinairair.com', 'Itinairair'))
            ->to($this->adminMail)
            ->text('');
        $email->getHeaders()->addTextHeader('templateId', '1');

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send demo created email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
