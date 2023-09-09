<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Avatars\AvatarCreator;
use App\Base64\Base64Decoder;
use App\Base64\Base64Encoder;
use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Repositories\SessionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Nette\Utils\ImageException;

class UploadAvatarPersister
{
    private ApiToken $apiToken;

    private AvatarCreator $avatarCreator;

    private Base64Decoder $base64Decoder;

    private Base64Encoder $base64Encoder;

    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        AvatarCreator $avatarCreator,
        Base64Decoder $base64Decoder,
        Base64Encoder $base64Encoder,
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->avatarCreator = $avatarCreator;
        $this->base64Decoder = $base64Decoder;
        $this->base64Encoder = $base64Encoder;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws ImageException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function uploadAvatar(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $sourceString = $this->base64Decoder->decode($requestData['avatar']);
        $avatarString = $this->avatarCreator->create($sourceString);
        $base64String = $this->base64Encoder->encode($avatarString);
        $requestData['avatar'] = $base64String;
        $user->setAvatar($requestData['avatar']);
        $user->setUpdatedAt($now);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
