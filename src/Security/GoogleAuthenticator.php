<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MailerHelper;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserRepository
     */
    private $userManager;
    /**
     * @var MailerHelper
     */
    private $mailerHelper;
    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, MailerHelper $mailerHelper, TelegramBotHelper $telegramBotHelper
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->mailerHelper = $mailerHelper;
        $this->telegramBotHelper = $telegramBotHelper;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    /**
     * @param Request $request
     *
     * @return AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        // this method is only called if supports() returns true

        return $this->fetchAccessToken($this->getGoogleClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $googleUser->getEmail()]);

        if (!$user) {
            $user = new User();
            $user->setUsername($email);
            $user->setEmail($email);

            $this->em->persist($user);
            $this->em->flush();

            $this->mailerHelper->sendNewUserMail($user);
            $this->telegramBotHelper->sendNewUserMessage($_ENV['ANNOUNCE_GROUP_ID_ADMIN'], $user);
        }

        return $user;
    }

    /**
     * @return GoogleClient
     */
    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     * @param string $providerKey
     *
     * @return null|Response
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ) {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $providerKey
        )
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('default'));
    }

    /**
     * @return null|Response
     */
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ) {
        $message = strtr(
            $exception->getMessageKey(),
            $exception->getMessageData()
        );

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     *
     * @return RedirectResponse
     */
    public function start(
        Request $request,
        AuthenticationException $authException = null
    ) {
        return new RedirectResponse(
            '/connect/',
            // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
