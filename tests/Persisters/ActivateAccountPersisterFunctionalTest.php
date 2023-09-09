<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\ActivateAccountPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class ActivateAccountPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testActivateAccount(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'jake.doe@example.com';

            /** @var ActivateAccountPersister $activateAccountPersister */
            $activateAccountPersister = $dic->get(ActivateAccountPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $activateAccountPersister->activateAccount(['email' => $emailAddress]);
            $this->assertNull($userToCheck->getToken());
            $this->assertTrue($userToCheck->isActive());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertNull($userFromDatabase->getToken());
            $this->assertTrue($userFromDatabase->isActive());
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
