<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OAuthRegistrationService
{
    public function __construct(private UserRepository $repository)
    {

    }

    /**
     * @param GoogleUser $resourceOwner
     *
     */
    public function persist(ResourceOwnerInterface $resourceOwner): User
    {
        $user = (new User())
            ->setEmail($resourceOwner->getEmail())
            ->setGoogleId($resourceOwner->getId())
            ->setFirstName($resourceOwner->getFirstName())
            ->setIsVerified(true)
            ->setRoles(['ROLE_USER']);
        $this->repository->add($user, true);

        return $user;
    }
}