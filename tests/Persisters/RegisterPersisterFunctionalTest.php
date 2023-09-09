<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Base64\Base64Decoder;
use App\Base64\Base64Encoder;
use App\Entities\Password;
use App\Entities\Token;
use App\Entities\User;
use App\EntityFactories\UserFactory;
use App\Exceptions\CouldNotPersistException;
use App\Generators\TokenGenerator;
use App\Images\ImageFactory;
use App\Passwords\PasswordAlgorithms;
use App\Persisters\RegisterPersister;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\TokenGeneratorWithPredefinedToken;
use Tests\UserFactoryWithPredefinedToken;
use Throwable;

final class RegisterPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testCreateUser(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            /** @var RegisterPersister $registerPersister */
            $registerPersister = $dic->get(RegisterPersister::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            /** @var Base64Encoder $base64Encoder */
            $base64Encoder = $dic->get(Base64Encoder::class);

            /** @var Base64Decoder $base64Decoder */
            $base64Decoder = $dic->get(Base64Decoder::class);

            /** @var ImageFactory $imageFactory */
            $imageFactory = $dic->get(ImageFactory::class);

            $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
            $base64String = $base64Encoder->encode($sourceString);

            $requestData = [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'avatar' => $base64String,
                'email' => 'extra-new-john-doe@example.com',
                'password' => 'secret',
                'timezone' => 'Europe/Prague',
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $registerPersister->createUser($requestData);
            $this->assertInstanceOf(User::class, $userToCheck);
            $this->assertNotNull($userToCheck->getId());
            $this->assertSame('John', $userToCheck->getFirstName());
            $this->assertSame('Doe', $userToCheck->getLastName());
            $iconToCheck = $imageFactory->createImage($base64Decoder->decode($userToCheck->getAvatar()));
            $this->assertSame(256, $iconToCheck->getWidth());
            $this->assertSame(256, $iconToCheck->getHeight());
            $this->assertSame('extra-new-john-doe@example.com', $userToCheck->getEmail());
            $this->assertInstanceOf(Password::class, $userToCheck->getPassword());
            $this->assertSame(PasswordAlgorithms::BCRYPT, $userToCheck->getPassword()->getAlgorithm());
            $this->assertFalse($userToCheck->isTeacher());
            $this->assertFalse($userToCheck->isStudent());
            $this->assertSame('Europe/Prague', $userToCheck->getTimezone());
            $this->assertInstanceOf(Token::class, $userToCheck->getToken());
            $this->assertSame(
                $userToCheck->getCreatedAt()->getTimestamp(),
                $userToCheck->getToken()->getCreatedAt()->getTimestamp()
            );
            $this->assertNull($userToCheck->getSecurityCode());
            $this->assertSame(0, $userToCheck->getAuthenticationFailures());
            $this->assertFalse($userToCheck->isLocked());
            $this->assertFalse($userToCheck->isActive());
            $this->assertSame(
                $userToCheck->getCreatedAt()->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertInstanceOf(User::class, $userFromDatabase);
            $this->assertNotNull($userFromDatabase->getId());
            $this->assertSame('John', $userFromDatabase->getFirstName());
            $this->assertSame('Doe', $userFromDatabase->getLastName());
            $iconFromDatabase = $imageFactory->createImage($base64Decoder->decode($userFromDatabase->getAvatar()));
            $this->assertSame(256, $iconFromDatabase->getWidth());
            $this->assertSame(256, $iconFromDatabase->getHeight());
            $this->assertSame('extra-new-john-doe@example.com', $userFromDatabase->getEmail());
            $this->assertInstanceOf(Password::class, $userFromDatabase->getPassword());
            $this->assertSame(PasswordAlgorithms::BCRYPT, $userFromDatabase->getPassword()->getAlgorithm());
            $this->assertFalse($userFromDatabase->isTeacher());
            $this->assertFalse($userFromDatabase->isStudent());
            $this->assertSame('Europe/Prague', $userFromDatabase->getTimezone());
            $this->assertInstanceOf(Token::class, $userFromDatabase->getToken());
            $this->assertSame(
                $userFromDatabase->getCreatedAt()->getTimestamp(),
                $userFromDatabase->getToken()->getCreatedAt()->getTimestamp()
            );
            $this->assertNull($userFromDatabase->getSecurityCode());
            $this->assertSame(0, $userFromDatabase->getAuthenticationFailures());
            $this->assertFalse($userFromDatabase->isLocked());
            $this->assertFalse($userFromDatabase->isActive());
            $this->assertSame(
                $userFromDatabase->getCreatedAt()->getTimestamp(),
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
    public function testCreateUserThrowsExceptionBecauseOfDuplicityInEmail(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        /** @var RegisterPersister $registerPersister */
        $registerPersister = $dic->get(RegisterPersister::class);

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        $requestData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'avatar' => $base64String,
            'email' => 'john.doe@example.com',
            'password' => 'secret',
            'timezone' => 'Europe/Prague',
        ];

        try {
            $registerPersister->createUser($requestData);
            $this->fail('Failed to throw exception.');
        } catch (CouldNotPersistException $e) {
            $data = $e->getData();
            $this->assertSame(14, $data['error']['code']);
            $this->assertSame("Value for 'email' in request body is already taken.", $data['error']['message']);
            $this->assertSame("Value for 'email' in request body is already taken.", $e->getMessage());
        }
    }

    /**
     * @throws Throwable
     */
    public function testCreateUserThrowsExceptionBecauseOfDuplicityInToken(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        /** @var TokenGenerator $tokenGenerator */
        $tokenGenerator = $dic->get(TokenGeneratorWithPredefinedToken::class);
        $dic->set(TokenGenerator::class, $tokenGenerator);

        /** @var UserFactory $userFactory */
        $userFactory = $dic->get(UserFactoryWithPredefinedToken::class);
        $dic->set(UserFactory::class, $userFactory);

        /** @var RegisterPersister $registerPersister */
        $registerPersister = $dic->get(RegisterPersister::class);

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        $requestData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'avatar' => $base64String,
            'email' => 'extra-new-john-doe@example.com',
            'password' => 'secret',
            'timezone' => 'Europe/Prague',
        ];

        try {
            $registerPersister->createUser($requestData);
            $this->fail('Failed to throw exception.');
        } catch (CouldNotPersistException $e) {
            $data = $e->getData();
            $this->assertSame(25, $data['error']['code']);
            $this->assertSame("Could not generate unique value for 'token' in 5 tries.", $data['error']['message']);
            $this->assertSame("Could not generate unique value for 'token' in 5 tries.", $e->getMessage());
        }
    }
}
