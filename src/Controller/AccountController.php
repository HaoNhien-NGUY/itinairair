<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAccountType;
use App\Form\UserAvatarType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account', methods: ['POST', 'GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserAccountType::class, $user);
        $avatarForm = $this->createForm(UserAvatarType::class, $user);
        $avatarForm->handleRequest($request);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid())
            || ($avatarForm->isSubmitted() && $avatarForm->isValid())) {
            $entityManager->flush();

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/index.html.twig', [
            'form' => $form,
            'avatarForm' => $avatarForm,
        ]);
    }
}
