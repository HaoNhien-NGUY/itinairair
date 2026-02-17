<?php

namespace App\Tests\Service;

use App\Service\MailerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerServiceTest extends TestCase
{
    private MailerInterface&MockObject $mailerMock;
    private LoggerInterface&MockObject $loggerMock;

    public function setUp(): void
    {
        $this->mailerMock = $this->createMock(MailerInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    public function testSendNewDemoCreatedSendsCorrectEmail(): void
    {
        $adminEmail = 'admin@test.com';

        $this->mailerMock->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) use ($adminEmail) {
                return $email->getTo()[0]->getAddress() === $adminEmail
                    && 'contact@itinairair.com' === $email->getFrom()[0]->getAddress()
                    && '1' === $email->getHeaders()->get('templateId')->getBodyAsString();
            }));

        $service = new MailerService($adminEmail, $this->mailerMock, $this->loggerMock);

        $service->sendNewDemoCreated();
    }

    public function testSendNewDemoCreatedLogsErrorOnException(): void
    {
        $this->mailerMock->expects($this->once())
            ->method('send')
            ->willThrowException(new TransportException('SMTP Error'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                'Failed to send demo created email',
                $this->callback(function ($context) {
                    return isset($context['error']) && 'SMTP Error' === $context['error'];
                })
            );

        $service = new MailerService('admin@test.com', $this->mailerMock, $this->loggerMock);

        $service->sendNewDemoCreated();
    }
}
