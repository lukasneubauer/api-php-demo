<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Exceptions\CouldNotPersistException;
use App\Generators\TokenGenerator;
use App\Persisters\CreateNewTokenPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\TokenGeneratorWithPredefinedToken;
use Throwable;

final class CreateNewTokenPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testCreateNewToken(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $emailAddress = 'john.doe@example.com';

            /** @var CreateNewTokenPersister $createNewTokenPersister */
            $createNewTokenPersister = $dic->get(CreateNewTokenPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $code = $user->getToken()->getCode();
            $codeCreatedAt = $user->getToken()->getCreatedAt();
            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $createNewTokenPersister->createNewToken(['email' => $emailAddress]);
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

    /**
     * @throws Throwable
     */
    public function testCreateNewTokenThrowsException(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        $emailAddress = 'nora.doe@example.com';

        /** @var TokenGenerator $tokenGenerator */
        $tokenGenerator = $dic->get(TokenGeneratorWithPredefinedToken::class);
        $dic->set(TokenGenerator::class, $tokenGenerator);

        /** @var CreateNewTokenPersister $createNewTokenPersister */
        $createNewTokenPersister = $dic->get(CreateNewTokenPersister::class);

        try {
            $createNewTokenPersister->createNewToken(['email' => $emailAddress]);
            $this->fail('Failed to throw exception.');
        } catch (CouldNotPersistException $e) {
            $data = $e->getData();
            $this->assertSame(25, $data['error']['code']);
            $this->assertSame("Could not generate unique value for 'token' in 5 tries.", $data['error']['message']);
            $this->assertSame("Could not generate unique value for 'token' in 5 tries.", $e->getMessage());
        }
    }
}
