<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\LockAccountPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class LockAccountPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testLockAccount(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'zack.doe@example.com';

            /** @var LockAccountPersister $lockAccountPersister */
            $lockAccountPersister = $dic->get(LockAccountPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $lockAccountPersister->lockAccount(['email' => $emailAddress]);
            $this->assertSame(3, $userToCheck->getAuthenticationFailures());
            $this->assertTrue($userToCheck->isLocked());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertSame(3, $userFromDatabase->getAuthenticationFailures());
            $this->assertTrue($userFromDatabase->isLocked());
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
