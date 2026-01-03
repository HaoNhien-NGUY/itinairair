<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'app_connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry, Request $request): Response
    {
        $options = [];

//        if ($lastEmail = $request->cookies->get('last_google_login_email')) {
//            $options['login_hint'] = $lastEmail;
//        }

        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'profile', 'email'
            ], $options);
    }

    #[Route('/connect/google/check', name: 'app_connect_google_check')]
    public function connectCheckAction(): void
    {
    }
}
