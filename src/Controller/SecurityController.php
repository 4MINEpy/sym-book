<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
    #[Route(path: '/oauthSB/check/{service}', name: 'auth_oauth_check',methods: ['GET'])]

    public function check(): Response
    {
        return new Response(status:200);
    }
}
