<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DateTime\DateTimeUTC;
use App\Entities\Session;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SessionRepository
{
    private EntityRepository $entityRepository;

    private DateTimeUTC $dateTimeUTC;

    public function __construct(EntityManagerInterface $em, DateTimeUTC $dateTimeUTC)
    {
        $this->entityRepository = $em->getRepository(Session::class);
        $this->dateTimeUTC = $dateTimeUTC;
    }

    public function getByApiClientId(string $apiClientId): ?Session
    {
        return $this->entityRepository->findOneBy(['apiClientId' => $apiClientId]);
    }

    public function getByApiToken(string $apiToken): ?Session
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('s');
        $queryBuilder->where('s.oldApiToken = :apiToken AND s.refreshedAt > :oldApiTokenEndOfLife')
            ->orWhere('s.currentApiToken = :apiToken')
            ->setParameters(
                [
                    'apiToken' => $apiToken,
                    'oldApiTokenEndOfLife' => $this->dateTimeUTC->createDateTimeInstance(\sprintf('- %s sec', Session::OLD_API_TOKEN_EXPIRATION_IN_SECONDS)),
                ]
            );

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
