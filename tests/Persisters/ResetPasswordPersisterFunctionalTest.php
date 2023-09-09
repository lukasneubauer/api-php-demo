<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Passwords\PasswordAlgorithms;
use App\Persisters\ResetPasswordPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class ResetPasswordPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testResetPassword(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'jane.doe@example.com';

            /** @var ResetPasswordPersister $resetPasswordPersister */
            $resetPasswordPersister = $dic->get(ResetPasswordPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $password = $user->getPassword();

            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'userId' => '912ff62e-fef5-442a-9953-b7c18dca9dae',
                'password' => 'new-secret',
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $resetPasswordPersister->resetPassword($requestData);

            $this->assertNull($userToCheck->getToken());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            $passwordToCheck = $userToCheck->getPassword();

            $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordToCheck->getAlgorithm());
            $this->assertNotSame($password->getHash(), $passwordToCheck->getHash());

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());

            $this->assertNull($userFromDatabase->getToken());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $passwordFromDatabase = $userFromDatabase->getPassword();

            $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordFromDatabase->getAlgorithm());
            $this->assertNotSame($password->getHash(), $passwordFromDatabase->getHash());
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
