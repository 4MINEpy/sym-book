<?php

namespace App\Security;

use App\Repository\UserRepository;
use http\Exception\RuntimeException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class GoogleAuthenticator extends AbstractOAuthAthenticator
{
   protected string $serviceName = 'google';
    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner,UserRepository $repository){
            //on verifie s'il appartient a notre provider (dans ce cas google)
            if(!($resourceOwner instanceof GoogleUser)){
                    throw new RuntimeException("expecting a google user ");
            }
            //on verifie si l'email est verfiee(il peut appartenir a google mais pas verfie
            if(true !== ($resourceOwner->toArray()['email_verified']?? null)){
                throw new \MongoDB\Driver\Exception\AuthenticationException("email not verified");
            }
            return $repository->findOneBy(['google_id'=>$resourceOwner->getId(),'email'=>$resourceOwner->getEmail()]);
    }//pour voir sil exist un user avec ce mail sinon on cree le user
}
