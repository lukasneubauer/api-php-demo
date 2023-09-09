<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Exceptions\CouldNotPersistException;
use App\Http\ApiHeaders;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordSettings;
use App\Persisters\SettingsPersister;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\PasswordSettingsWithPredefinedValues;
use Throwable;

/**
 * This test is calling tearDown() method instead of using try..catch..finally because of entity manager locking.
 */
final class SettingsPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testUpdateSettings(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

        $request = new Request();
        $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

        /** @var RequestStack $requestStack */
        $requestStack = $dic->get(RequestStack::class);
        $requestStack->push($request);

        /** @var SettingsPersister $settingsPersister */
        $settingsPersister = $dic->get(SettingsPersister::class);

        /** @var SessionRepository $sessionRepository */
        $sessionRepository = $dic->get(SessionRepository::class);

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $session = $sessionRepository->getByApiToken($apiToken);

        $user = $session->getUser();

        $password = $user->getPassword();

        $userUpdatedAt = $user->getUpdatedAt();

        $requestData = [
            'firstName' => 'Frank',
            'lastName' => 'Sinatra',
            'email' => 'frank.sinatra@example.com',
            'password' => null,
            'isTeacher' => true,
            'timezone' => 'Europe/Prague',
        ];

        EntityManagerCleanup::cleanupEntityManager($dic);

        $userToCheck = $settingsPersister->updateSettings($requestData);

        $this->assertSame('Frank', $userToCheck->getFirstName());
        $this->assertSame('Sinatra', $userToCheck->getLastName());
        $this->assertSame('frank.sinatra@example.com', $userToCheck->getEmail());
        $this->assertTrue($userToCheck->isTeacher());
        $this->assertSame('Europe/Prague', $userToCheck->getTimezone());
        $this->assertGreaterThan(
            $userUpdatedAt->getTimestamp(),
            $userToCheck->getUpdatedAt()->getTimestamp()
        );

        $passwordToCheck = $userToCheck->getPassword();

        $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordToCheck->getAlgorithm());
        $this->assertSame($password->getHash(), $passwordToCheck->getHash());

        EntityManagerCleanup::cleanupEntityManager($dic);

        $userFromDatabase = $userRepository->getById($userToCheck->getId());

        $this->assertSame('Frank', $userFromDatabase->getFirstName());
        $this->assertSame('Sinatra', $userFromDatabase->getLastName());
        $this->assertSame('frank.sinatra@example.com', $userFromDatabase->getEmail());
        $this->assertTrue($userFromDatabase->isTeacher());
        $this->assertSame('Europe/Prague', $userFromDatabase->getTimezone());
        $this->assertGreaterThan(
            $userUpdatedAt->getTimestamp(),
            $userFromDatabase->getUpdatedAt()->getTimestamp()
        );

        $passwordFromDatabase = $userFromDatabase->getPassword();

        $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordFromDatabase->getAlgorithm());
        $this->assertSame($password->getHash(), $passwordFromDatabase->getHash());
    }

    /**
     * @throws Throwable
     */
    public function testUpdateSettingsAlsoUpdatesPassword(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

        $request = new Request();
        $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

        /** @var RequestStack $requestStack */
        $requestStack = $dic->get(RequestStack::class);
        $requestStack->push($request);

        /** @var SettingsPersister $settingsPersister */
        $settingsPersister = $dic->get(SettingsPersister::class);

        /** @var SessionRepository $sessionRepository */
        $sessionRepository = $dic->get(SessionRepository::class);

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $session = $sessionRepository->getByApiToken($apiToken);

        $user = $session->getUser();

        $password = $user->getPassword();

        $userUpdatedAt = $user->getUpdatedAt();

        $requestData = [
            'firstName' => 'Frank',
            'lastName' => 'Sinatra',
            'email' => 'frank.sinatra@example.com',
            'password' => 'secret',
            'isTeacher' => true,
            'timezone' => 'Europe/Prague',
        ];

        EntityManagerCleanup::cleanupEntityManager($dic);

        $userToCheck = $settingsPersister->updateSettings($requestData);

        $this->assertSame('Frank', $userToCheck->getFirstName());
        $this->assertSame('Sinatra', $userToCheck->getLastName());
        $this->assertSame('frank.sinatra@example.com', $userToCheck->getEmail());
        $this->assertTrue($userToCheck->isTeacher());
        $this->assertSame('Europe/Prague', $userToCheck->getTimezone());
        $this->assertGreaterThan(
            $userUpdatedAt->getTimestamp(),
            $userToCheck->getUpdatedAt()->getTimestamp()
        );

        $passwordToCheck = $userToCheck->getPassword();

        $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordToCheck->getAlgorithm());
        $this->assertNotSame($password->getHash(), $passwordToCheck->getHash());

        EntityManagerCleanup::cleanupEntityManager($dic);

        $userFromDatabase = $userRepository->getById($userToCheck->getId());

        $this->assertSame('Frank', $userFromDatabase->getFirstName());
        $this->assertSame('Sinatra', $userFromDatabase->getLastName());
        $this->assertSame('frank.sinatra@example.com', $userFromDatabase->getEmail());
        $this->assertTrue($userFromDatabase->isTeacher());
        $this->assertSame('Europe/Prague', $userFromDatabase->getTimezone());
        $this->assertGreaterThan(
            $userUpdatedAt->getTimestamp(),
            $userFromDatabase->getUpdatedAt()->getTimestamp()
        );

        $passwordFromDatabase = $userFromDatabase->getPassword();

        $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordFromDatabase->getAlgorithm());
        $this->assertNotSame($password->getHash(), $passwordFromDatabase->getHash());
    }

    /**
     * @throws Throwable
     */
    public function testUpdateSettingsAlsoUpdatesPasswordToNewAlgorithm(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

        $request = new Request();
        $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

        /** @var RequestStack $requestStack */
        $requestStack = $dic->get(RequestStack::class);
        $requestStack->push($request);

        /** @var PasswordSettings $passwordSettings */
        $passwordSettings = $dic->get(PasswordSettingsWithPredefinedValues::class);
        $dic->set(PasswordSettings::class, $passwordSettings);

        /** @var SettingsPersister $settingsPersister */
        $settingsPersister = $dic->get(SettingsPersister::class);

        /** @var SessionRepository $sessionRepository */
        $sessionRepository = $dic->get(SessionRepository::class);

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $session = $sessionRepository->getByApiToken($apiToken);

        $user = $session->getUser();

        $password = $user->getPassword();

        $userUpdatedAt = $user->getUpdatedAt();

        $requestData = [
            'firstName' => 'Frank',
            'lastName' => 'Sinatra',
            'email' => 'frank.sinatra@example.com',
            'password' => 'secret',
            'isTeacher' => true,
            'timezone' => 'Europe/Prague',
        ];

        EntityManagerCleanup::cleanupEntityManager($dic);

        $userToCheck = $settingsPersister->updateSettings($requestData);

        $this->assertSame('Frank', $userToCheck->getFirstName());
        $this->assertSame('Sinatra', $userToCheck->getLastName());
        $this->assertSame('frank.sinatra@example.com', $userToCheck->getEmail());
        $this->assertTrue($userToCheck->isTeacher());
        $this->assertSame('Europe/Prague', $userToCheck->getTimezone());
        $this->assertGreaterThan(
            $userUpdatedAt->getTimestamp(),
            $userToCheck->getUpdatedAt()->getTimestamp()
        );

        $passwordToCheck = $userToCheck->getPassword();

        $this->assertSame(PasswordAlgorithms::ARGON2I, $passwordToCheck->getAlgorithm());
        $this->assertNotSame($password->getHash(), $passwordToCheck->getHash());

        EntityManagerCleanup::cleanupEntityManager($dic);

        $userFromDatabase = $userRepository->getById($userToCheck->getId());

        $this->assertSame('Frank', $userFromDatabase->getFirstName());
        $this->assertSame('Sinatra', $userFromDatabase->getLastName());
        $this->assertSame('frank.sinatra@example.com', $userFromDatabase->getEmail());
        $this->assertTrue($userFromDatabase->isTeacher());
        $this->assertSame('Europe/Prague', $userFromDatabase->getTimezone());
        $this->assertGreaterThan(
            $userUpdatedAt->getTimestamp(),
            $userFromDatabase->getUpdatedAt()->getTimestamp()
        );

        $passwordFromDatabase = $userFromDatabase->getPassword();

        $this->assertSame(PasswordAlgorithms::ARGON2I, $passwordFromDatabase->getAlgorithm());
        $this->assertNotSame($password->getHash(), $passwordFromDatabase->getHash());
    }

    /**
     * @throws Throwable
     */
    public function testUpdateSettingsThrowsException(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

        $request = new Request();
        $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

        /** @var RequestStack $requestStack */
        $requestStack = $dic->get(RequestStack::class);
        $requestStack->push($request);

        /** @var SettingsPersister $settingsPersister */
        $settingsPersister = $dic->get(SettingsPersister::class);

        try {
            $requestData = [
                'firstName' => 'Jake',
                'lastName' => 'Doe',
                'email' => 'jake.doe@example.com',
                'password' => 'secret',
                'isTeacher' => true,
                'timezone' => 'Europe/Prague',
            ];

            $settingsPersister->updateSettings($requestData);

            $this->fail('Failed to throw exception.');
        } catch (CouldNotPersistException $e) {
            $data = $e->getData();
            $this->assertSame(14, $data['error']['code']);
            $this->assertSame("Value for 'email' in request body is already taken.", $data['error']['message']);
            $this->assertSame("Value for 'email' in request body is already taken.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        Database::resetDatabase($dic);
    }
}
