<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DemoGeneratorService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DemoController extends AbstractController
{
    #[Route('/demo/start', name: 'app_demo_start', methods: ['POST'])]
    public function start(
        Request                $request,
        EntityManagerInterface $entityManager,
        DemoGeneratorService   $demoGenerator,
        Security               $security,
        TranslatorInterface    $translator,
        MailerService          $mailer,
    ): Response {
        if($this->getUser()) {
            return $this->redirectToRoute('app_trip');
        }

        if (!$this->isCsrfTokenValid('start_demo', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_home');
        }

        $user = (new User())
            ->setExpiresAt(new \DateTimeImmutable('+1 hour'))
            ->setEmail('demo_'.uniqid() . '@temp')
            ->setUsername($translator->trans('trip.demo.username'));
        $entityManager->persist($user);
        $entityManager->flush();

        $demoGenerator->generateDemoTrip($user);
        $entityManager->flush();

        $mailer->sendNewDemoCreated();

        $security->login($user, 'form_login', 'main');

        return $this->redirectToRoute('app_trip');
    }
}
