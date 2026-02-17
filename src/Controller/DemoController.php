<?php

namespace App\Controller;

use App\Service\DemoGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DemoController extends AbstractController
{
    #[Route('/demo/start', name: 'app_demo_start', methods: ['POST'])]
    public function start(
        Request $request,
        DemoGeneratorService $demoGenerator,
        Security $security,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_trip');
        }

        if (!$this->isCsrfTokenValid('start_demo', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_home');
        }

        $demoUser = $demoGenerator->generateDemo();

        $security->login($demoUser, 'form_login', 'main');

        return $this->redirectToRoute('app_trip');
    }
}
