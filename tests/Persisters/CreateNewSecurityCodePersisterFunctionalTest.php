<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\CreateNewSecurityCodePersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class CreateNewSecurityCodePersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testCreateNewSecurityCode(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'seth.doe@example.com';

            /** @var CreateNewSecurityCodePersister $createNewSecurityCodePersister */
            $createNewSecurityCodePersister = $dic->get(CreateNewSecurityCodePersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $code = $user->getSecurityCode()->getCode();
            $codeCreatedAt = $user->getSecurityCode()->getCreatedAt();
            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $createNewSecurityCodePersister->createNewSecurityCode(['email' => $emailAddress]);
            $this->assertNotSame($code, $userToCheck->getSecurityCode()->getCode());
            $this->assertGreaterThan(
                $codeCreatedAt->getTimestamp(),
                $userToCheck->getSecurityCode()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userToCheck->getSecurityCode()->getCreatedAt()->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertNotSame($code, $userFromDatabase->getSecurityCode()->getCode());
            $this->assertGreaterThan(
                $codeCreatedAt->getTimestamp(),
                $userFromDatabase->getSecurityCode()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userFromDatabase->getSecurityCode()->getCreatedAt()->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
