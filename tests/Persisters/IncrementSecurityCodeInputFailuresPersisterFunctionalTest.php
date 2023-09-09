<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\IncrementSecurityCodeInputFailuresPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class IncrementSecurityCodeInputFailuresPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testIncrementSecurityCodeInputFailures(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'nina.doe@example.com';

            /** @var IncrementSecurityCodeInputFailuresPersister $incrementSecurityCodeInputFailuresPersister */
            $incrementSecurityCodeInputFailuresPersister = $dic->get(IncrementSecurityCodeInputFailuresPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $incrementSecurityCodeInputFailuresPersister->incrementSecurityCodeInputFailures(['email' => $emailAddress]);
            $this->assertSame(1, $userToCheck->getSecurityCode()->getInputFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertSame(1, $userFromDatabase->getSecurityCode()->getInputFailures());
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
