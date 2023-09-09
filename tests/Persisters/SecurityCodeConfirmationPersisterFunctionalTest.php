<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\SecurityCodeConfirmationPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class SecurityCodeConfirmationPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testConfirmSecurityCode(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'nina.doe@example.com';

            /** @var SecurityCodeConfirmationPersister $securityCodeConfirmationPersister */
            $securityCodeConfirmationPersister = $dic->get(SecurityCodeConfirmationPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $securityCodeConfirmationPersister->confirmSecurityCode(['email' => $emailAddress]);
            $this->assertNull($userToCheck->getSecurityCode());
            $this->assertSame(0, $userToCheck->getAuthenticationFailures());
            $this->assertFalse($userToCheck->isLocked());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertNull($userFromDatabase->getSecurityCode());
            $this->assertSame(0, $userFromDatabase->getAuthenticationFailures());
            $this->assertFalse($userFromDatabase->isLocked());
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
