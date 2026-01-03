<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class GoogleAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly ClientRegistry         $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface        $urlGenerator,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        if ($request->query->get('error')) {
            throw new CustomUserMessageAuthenticationException('auth.google.canceled');
        }

        $client = $this->clientRegistry->getClient('google');

        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUser();

        return new SelfValidatingPassport(
            new UserBadge($googleUser->getEmail(), function() use ($googleUser, $client) {
                $email = $googleUser->getEmail();

                if (!$email) {
                    throw new AuthenticationException();
                }

                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($existingUser) {
                    return $existingUser;
                }

                $user = new User();
                $user->setEmail($email);
                $user->setUsername($googleUser->getFirstName());
                $user->setAvatarUrl($googleUser->getAvatar() ?? null);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName) ?: $this->urlGenerator->generate('app_trip');
        $response = new RedirectResponse($targetPath);

        /** @var User $user */
        $user = $token->getUser();

        $cookie = Cookie::create('last_google_login_email')
            ->withValue($user->getEmail())
            ->withExpires(new \DateTime('+1 year'))
            ->withHttpOnly()
            ->withSecure();

        $response->headers->setCookie($cookie);

        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
