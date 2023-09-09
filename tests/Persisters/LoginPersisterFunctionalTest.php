<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\EntityFactories\SessionFactory;
use App\Exceptions\CouldNotPersistException;
use App\Generators\ApiTokenGenerator;
use App\Http\ApiHeaders;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordSettings;
use App\Persisters\LoginPersister;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\ApiTokenGeneratorWithPredefinedApiToken;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\PasswordSettingsWithPredefinedValues;
use Tests\SessionFactoryWithPredefinedApiToken;
use Throwable;

final class LoginPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testCreateSession(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiClientId = 'CLIENT-ID';
            $emailAddress = 'john.doe@example.com';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_CLIENT_ID, $apiClientId);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var LoginPersister $loginPersister */
            $loginPersister = $dic->get(LoginPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $password = $user->getPassword();

            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'email' => $emailAddress,
                'password' => 'secret',
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionToCheck = $loginPersister->createSession($requestData);
            $this->assertNotNull($sessionToCheck->getId());
            $this->assertSame($user->getId(), $sessionToCheck->getUser()->getId());
            $this->assertSame($apiClientId, $sessionToCheck->getApiClientId());
            $this->assertNull($sessionToCheck->getOldApiToken());
            $this->assertNotNull($sessionToCheck->getCurrentApiToken());
            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $sessionToCheck->getRefreshedAt()->getTimestamp()
            );
            $this->assertFalse($sessionToCheck->isLocked());
            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $sessionToCheck->getUpdatedAt()->getTimestamp()
            );

            $userToCheck = $sessionToCheck->getUser();
            $this->assertSame($password->getHash(), $userToCheck->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::BCRYPT, $userToCheck->getPassword()->getAlgorithm());
            $this->assertSame(0, $userToCheck->getAuthenticationFailures());
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertNotSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionFromDatabase = $sessionRepository->getByApiToken($sessionToCheck->getCurrentApiToken());
            $this->assertNotNull($sessionFromDatabase->getId());
            $this->assertSame($user->getId(), $sessionFromDatabase->getUser()->getId());
            $this->assertSame($apiClientId, $sessionFromDatabase->getApiClientId());
            $this->assertNull($sessionFromDatabase->getOldApiToken());
            $this->assertNotNull($sessionFromDatabase->getCurrentApiToken());
            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
                $sessionFromDatabase->getRefreshedAt()->getTimestamp()
            );
            $this->assertFalse($sessionFromDatabase->isLocked());
            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
                $sessionFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $userFromDatabase = $sessionFromDatabase->getUser();
            $this->assertSame($password->getHash(), $userFromDatabase->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::BCRYPT, $userFromDatabase->getPassword()->getAlgorithm());
            $this->assertSame(0, $userFromDatabase->getAuthenticationFailures());
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertNotSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
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
    public function testCreateSessionWillRehashPassword(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiClientId = 'CLIENT-ID';
            $emailAddress = 'john.doe@example.com';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_CLIENT_ID, $apiClientId);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var PasswordSettings $passwordSettings */
            $passwordSettings = $dic->get(PasswordSettingsWithPredefinedValues::class);
            $dic->set(PasswordSettings::class, $passwordSettings);

            /** @var LoginPersister $loginPersister */
            $loginPersister = $dic->get(LoginPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $password = $user->getPassword();

            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'email' => $emailAddress,
                'password' => 'secret',
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionToCheck = $loginPersister->createSession($requestData);
            $this->assertNotNull($sessionToCheck->getId());
            $this->assertSame($user->getId(), $sessionToCheck->getUser()->getId());
            $this->assertSame($apiClientId, $sessionToCheck->getApiClientId());
            $this->assertNull($sessionToCheck->getOldApiToken());
            $this->assertNotNull($sessionToCheck->getCurrentApiToken());
            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $sessionToCheck->getRefreshedAt()->getTimestamp()
            );
            $this->assertFalse($sessionToCheck->isLocked());
            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $sessionToCheck->getUpdatedAt()->getTimestamp()
            );

            $userToCheck = $sessionToCheck->getUser();
            $this->assertNotSame($password->getHash(), $userToCheck->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::ARGON2I, $userToCheck->getPassword()->getAlgorithm());
            $this->assertSame(0, $userToCheck->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionFromDatabase = $sessionRepository->getByApiToken($sessionToCheck->getCurrentApiToken());
            $this->assertNotNull($sessionFromDatabase->getId());
            $this->assertSame($user->getId(), $sessionFromDatabase->getUser()->getId());
            $this->assertSame($apiClientId, $sessionFromDatabase->getApiClientId());
            $this->assertNull($sessionFromDatabase->getOldApiToken());
            $this->assertNotNull($sessionFromDatabase->getCurrentApiToken());
            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
                $sessionFromDatabase->getRefreshedAt()->getTimestamp()
            );
            $this->assertFalse($sessionFromDatabase->isLocked());
            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
                $sessionFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $userFromDatabase = $sessionFromDatabase->getUser();
            $this->assertNotSame($password->getHash(), $userFromDatabase->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::ARGON2I, $userFromDatabase->getPassword()->getAlgorithm());
            $this->assertSame(0, $userFromDatabase->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
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
    public function testCreateSessionWillResetAuthenticationFailures(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiClientId = 'CLIENT-ID';
            $emailAddress = 'zack.doe@example.com';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_CLIENT_ID, $apiClientId);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var LoginPersister $loginPersister */
            $loginPersister = $dic->get(LoginPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $user = $userRepository->getByEmail($emailAddress);

            $password = $user->getPassword();

            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'email' => $emailAddress,
                'password' => 'secret',
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionToCheck = $loginPersister->createSession($requestData);
            $this->assertNotNull($sessionToCheck->getId());
            $this->assertSame($user->getId(), $sessionToCheck->getUser()->getId());
            $this->assertSame($apiClientId, $sessionToCheck->getApiClientId());
            $this->assertNull($sessionToCheck->getOldApiToken());
            $this->assertNotNull($sessionToCheck->getCurrentApiToken());
            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $sessionToCheck->getRefreshedAt()->getTimestamp()
            );
            $this->assertFalse($sessionToCheck->isLocked());
            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $sessionToCheck->getUpdatedAt()->getTimestamp()
            );

            $userToCheck = $sessionToCheck->getUser();
            $this->assertSame($password->getHash(), $userToCheck->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::BCRYPT, $userToCheck->getPassword()->getAlgorithm());
            $this->assertSame(0, $userToCheck->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $sessionToCheck->getCreatedAt()->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionFromDatabase = $sessionRepository->getByApiToken($sessionToCheck->getCurrentApiToken());
            $this->assertNotNull($sessionFromDatabase->getId());
            $this->assertSame($user->getId(), $sessionFromDatabase->getUser()->getId());
            $this->assertSame($apiClientId, $sessionFromDatabase->getApiClientId());
            $this->assertNull($sessionFromDatabase->getOldApiToken());
            $this->assertNotNull($sessionFromDatabase->getCurrentApiToken());
            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
                $sessionFromDatabase->getRefreshedAt()->getTimestamp()
            );
            $this->assertFalse($sessionFromDatabase->isLocked());
            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
                $sessionFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $userFromDatabase = $sessionFromDatabase->getUser();
            $this->assertSame($password->getHash(), $userFromDatabase->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::BCRYPT, $userFromDatabase->getPassword()->getAlgorithm());
            $this->assertSame(0, $userFromDatabase->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $sessionFromDatabase->getCreatedAt()->getTimestamp(),
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
    public function testCreateSessionThrowsException(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        $apiClientId = 'CLIENT-ID';
        $emailAddress = 'john.doe@example.com';

        $request = new Request();
        $request->headers->set(ApiHeaders::API_CLIENT_ID, $apiClientId);

        /** @var RequestStack $requestStack */
        $requestStack = $dic->get(RequestStack::class);
        $requestStack->push($request);

        /** @var ApiTokenGenerator $apiTokenGenerator */
        $apiTokenGenerator = $dic->get(ApiTokenGeneratorWithPredefinedApiToken::class);
        $dic->set(ApiTokenGenerator::class, $apiTokenGenerator);

        /** @var SessionFactory $sessionFactory */
        $sessionFactory = $dic->get(SessionFactoryWithPredefinedApiToken::class);
        $dic->set(SessionFactory::class, $sessionFactory);

        /** @var LoginPersister $loginPersister */
        $loginPersister = $dic->get(LoginPersister::class);

        $requestData = [
            'email' => $emailAddress,
            'password' => 'secret',
        ];

        try {
            $loginPersister->createSession($requestData);
            $this->fail('Failed to throw exception.');
        } catch (CouldNotPersistException $e) {
            $data = $e->getData();
            $this->assertSame(25, $data['error']['code']);
            $this->assertSame("Could not generate unique value for 'apiToken' in 5 tries.", $data['error']['message']);
            $this->assertSame("Could not generate unique value for 'apiToken' in 5 tries.", $e->getMessage());
        }
    }
}
