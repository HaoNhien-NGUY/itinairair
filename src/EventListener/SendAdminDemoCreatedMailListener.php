<?php

namespace App\EventListener;

use App\Event\DemoCreatedEvent;
use App\Service\MailerService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class SendAdminDemoCreatedMailListener
{
    public function __construct(private MailerService $mailer)
    {
    }

    #[AsEventListener]
    public function onDemoCreatedEvent(DemoCreatedEvent $event): void
    {
        $this->mailer->sendNewDemoCreated();
    }
}
