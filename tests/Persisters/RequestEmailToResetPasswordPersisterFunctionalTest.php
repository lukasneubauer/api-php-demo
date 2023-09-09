<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\RequestEmailToResetPasswordPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class RequestEmailToResetPasswordPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testRequestEmailToResetPassword(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'john.doe@example.com';

            /** @var RequestEmailToResetPasswordPersister $requestEmailToResetPasswordPersister */
            $requestEmailToResetPasswordPersister = $dic->get(RequestEmailToResetPasswordPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $code = $user->getToken()->getCode();
            $codeCreatedAt = $user->getToken()->getCreatedAt();
            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $requestEmailToResetPasswordPersister->requestEmailToResetPassword(['email' => $emailAddress]);
            $this->assertNotSame($code, $userToCheck->getToken()->getCode());
            $this->assertGreaterThan(
                $codeCreatedAt->getTimestamp(),
                $userToCheck->getToken()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userToCheck->getToken()->getCreatedAt()->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertNotSame($code, $userFromDatabase->getToken()->getCode());
            $this->assertGreaterThan(
                $codeCreatedAt->getTimestamp(),
                $userFromDatabase->getToken()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userFromDatabase->getToken()->getCreatedAt()->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
