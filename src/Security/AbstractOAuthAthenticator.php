<?php
namespace App\Security;
use App\Repository\UserRepository;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Entity\User;

abstract class AbstractOAuthAthenticator extends OAuth2Authenticator {
    use TargetPathTrait;
    protected string $serviceName = '';
    //pour retourner la liste des clients
        public function __construct(
            private readonly ClientRegistry $clientRegistry,
            private readonly RouterInterface $router,
            private readonly UserRepository $repository,
        private readonly OAuthRegistrationService $registrationService,)
        {

        }
        //pour verifier si nous sommes dan la route de check
        public function supports(Request $request): ?bool
        {
            return 'auth_oauth_check' === $request->attributes->get('_route')&& $request->get('service_name') === $this->serviceName;//retourne le nom de service courant (en cas on a des services plus de google )
        }
        // si il essaye d'acceder a une page avant notre page de auth il sera redirectionner
        public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
        {
            $targetPath= $this->getTargetPath($request->getSession(), $firewallName);
            if($targetPath){
                return new RedirectResponse($targetPath);
            }
            return new RedirectResponse($this->router->generate('app_home'));
        }
        public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
        {
            if($request->hasSession()){
                $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
            }
            return new RedirectResponse($this->router->generate('app_login'));
        }
        public function authenticate(Request $request): Passport
        {
            //recuperer le access token
            $credentials= $this->fetchAccessToken($this->getClient());
            $resourceOwner=$this->getResourceOwnerFromCredentials($credentials);
            //recuperer le user
            $user=$this->getUserFromResourceOwner($resourceOwner,$this->repository);
            //en cas ou l'utilisateur n'existe pas on cree son compte
            if (null === $user) {
                $user = $this->registrationService->persist($resourceOwner);
            }
            //en cas il existe on retourne un passpost : un moyen pour confirmer une identite
            return new SelfValidatingPassport(
                new UserBadge($user->getUserIdentifier(), function ($userIdentifier) {
                    return $this->repository->findOneBy(['email' => $userIdentifier]);
                }),
                [new RememberMeBadge()]
            );


        }
        public function getClient():OAuth2ClientInterface
        {
            return $this->clientRegistry->getClient($this->serviceName);
        }
        //on va devoir demander les infos a notre provider : le client clique seconnecter avec google il est redergie vers le provider , le provider nous renvoit le token et nous utilisons le token pour recuperer les infos de l'utilisateurs
        protected function getResourceOwnerFromCredentials(AccessToken $credentials):ResourceOwnerInterface // cette interface definit les donnees que nous recuperons de google (notre service provider)
        {
                return $this->getClient()->fetchUserFromToken($credentials);
        }
        abstract protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner,UserRepository $userRepository);
}