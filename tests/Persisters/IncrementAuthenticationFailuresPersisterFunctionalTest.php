<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\IncrementAuthenticationFailuresPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class IncrementAuthenticationFailuresPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testIncrementAuthenticationFailures(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'hank.doe@example.com';

            /** @var IncrementAuthenticationFailuresPersister $incrementAuthenticationFailuresPersister */
            $incrementAuthenticationFailuresPersister = $dic->get(IncrementAuthenticationFailuresPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $incrementAuthenticationFailuresPersister->incrementAuthenticationFailures(['email' => $emailAddress]);
            $this->assertSame(1, $userToCheck->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertSame(1, $userFromDatabase->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
