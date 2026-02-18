<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\DemoGeneratorService;
use App\Tests\FunctionalTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DemoControllerTest extends FunctionalTestCase
{
    private DemoGeneratorService&MockObject $demoGenerator;
    private CsrfTokenManagerInterface&MockObject $csrfMock;
    private KernelBrowser $client;
    private User $generatedUser;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->generatedUser = $this->getUser();


        $this->demoGenerator = $this->createMock(DemoGeneratorService::class);
        static::getContainer()->set(DemoGeneratorService::class, $this->demoGenerator);

        $this->csrfMock = $this->createMock(CsrfTokenManagerInterface::class);
        static::getContainer()->set('security.csrf.token_manager', $this->csrfMock);
    }

    public function testUserAlreadyLoggedIn(): void
    {
        $this->createAuthenticatedClient($this->client);

        $this->client->request('POST', '/demo/start');

        $this->assertResponseRedirects('/trip/');
    }

    public function testWrongCsrfToken(): void
    {
        $this->csrfMock->method('isTokenValid')->willReturn(false);
        $this->demoGenerator->expects($this->never())->method('generateDemo');
        $this->client->request('POST', '/demo/start');

        $this->assertResponseRedirects('/');
    }

    public function testDemoStart(): void
    {
        $this->csrfMock->method('isTokenValid')->willReturn(true);
        $this->demoGenerator->expects($this->once())->method('generateDemo')->willReturn($this->generatedUser);

        $this->client->request('POST', '/demo/start');

        $this->assertResponseRedirects('/trip/');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $token = $this->client->getContainer()->get('security.token_storage')->getToken();
        $this->assertNotNull($token);
        $this->assertInstanceOf(User::class, $token->getUser());
    }
}
