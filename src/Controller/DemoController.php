<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DemoGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DemoController extends AbstractController
{
    #[Route('/demo/start', name: 'app_demo_start')]
    public function start(
        EntityManagerInterface $entityManager,
        DemoGeneratorService   $demoGenerator,
        Security               $security,
    ): Response {
        if($this->getUser()) {
            return $this->redirectToRoute('app_trip');
        }

        $user = (new User())
            ->setExpiresAt(new \DateTimeImmutable('+1 hour'))
            ->setEmail('demo_'.uniqid() . '@temp')
            ->setUsername('Demo');
        $entityManager->persist($user);
        $entityManager->flush();

        $trip = $demoGenerator->generateDemoTrip($user);
        $entityManager->flush();

        $security->login($user, 'form_login', 'main');

        return $this->redirectToRoute('app_trip');
    }
}
