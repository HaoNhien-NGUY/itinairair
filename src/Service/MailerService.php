<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        #[Autowire(env: 'APP_ADMIN_EMAIL')] private readonly string $adminMail,
        private readonly MailerInterface                            $mailer,
    ) {
    }

    public function sendNewDemoCreated(): void
    {
        $email = (new Email())
            ->from(new Address('contact@itinairair.com', 'Itinairair'))
            ->to($this->adminMail)
            ->text('');
        $email->getHeaders()->addTextHeader('templateId', 1);

        try {
            $this->mailer->send($email);
        } catch( \Exception) {
        }
    }
}
