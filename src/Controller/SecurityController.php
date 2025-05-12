<?php

namespace App\Controller;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\AbstractOAuthAthenticator;
use App\Security\GoogleAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class SecurityController extends AbstractController
{
    //POUR NE PAS DONNER CONFIANCES A L UTILISATEUR ON FAIT UNE CONST SCOPES AVEC LES SCOPES QUE JE VOUDRAI UTILISE
    public const SCOPES =[
        'google'=>[],
    ];
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route(path: '/oauth/connect/{service}', name: 'auth_oauth_connect',methods: ['GET'])]

    public function connect(string $service,ClientRegistry $clientRegistry): RedirectResponse
    {
            if(!in_array($service,array_keys(self::SCOPES),true)){
                throw $this->createNotFoundException();
            }
            return $clientRegistry->getClient($service)->redirect(self::SCOPES[$service]);
    }
    #[Route(path: '/oauthSB/check/{service}', name: 'auth_oauth_check', methods: ['GET'])]
    public function check(
        string $service,
        Request $request,
        UserAuthenticatorInterface $userAuthenticator,
        GoogleAuthenticator $authenticator,
        RouterInterface $router
    ): Response {
        try {
            // 1. Authenticate and get the Passport
            $passport = $authenticator->authenticate($request);

            // 2. Extract the user from the Passport
            $user = $passport->getUser();

            if (!$user instanceof UserInterface) {
                throw new AuthenticationException('Invalid user returned from authenticator');
            }

            // 3. Use UserAuthenticatorInterface to log in the user properly
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );

        } catch (AuthenticationException $exception) {
            // Authentication failure handling
            return $authenticator->onAuthenticationFailure($request, $exception);
        } catch (\Exception $exception) {
            // Other errors, redirect to login with error message
            return new RedirectResponse(
                $router->generate('app_login', ['error' => $exception->getMessage()]),
                Response::HTTP_TEMPORARY_REDIRECT
            );
        }
    }


}
