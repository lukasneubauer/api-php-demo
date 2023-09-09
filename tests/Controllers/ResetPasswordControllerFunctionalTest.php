<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordSettings;
use App\Repositories\UserRepository;
use Doctrine\Persistence\Mapping\MappingException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\ErrorResponseTester;
use Tests\HttpMethodsDataProvider;
use Tests\PasswordSettingsWithPredefinedValues;
use Tests\ResponseTester;

final class ResetPasswordControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/reset-password';

    public function testApiKeyIsMissing(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [],
            '',
            400,
            1,
            "Missing mandatory 'Api-Key' http header."
        );
    }

    public function testApiKeyIsEmpty(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => ''],
            '',
            400,
            2,
            "Missing value for 'Api-Key' http header."
        );
    }

    public function testApiKeyIsInvalid(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => 'xyz'],
            '',
            400,
            3,
            "Invalid value for 'Api-Key' http header."
        );
    }

    /**
     * @dataProvider getInvalidHttpMethods
     */
    public function testHttpMethodIsNotPost(string $invalidHttpMethod): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            $invalidHttpMethod,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '',
            400,
            4,
            "Usage of incorrect http method '$invalidHttpMethod'. 'POST' was expected."
        );
    }

    public static function getInvalidHttpMethods(): array
    {
        return HttpMethodsDataProvider::getHttpMethodsExcludingPost();
    }

    public function testMissingJsonInRequestBody(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '',
            400,
            8,
            'Missing JSON in request body.'
        );
    }

    public function testMalformedJsonInRequestBody(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{',
            400,
            9,
            'Malformed JSON in request body.'
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsUserId(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{}',
            400,
            10,
            "Missing mandatory property 'userId' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyUserId(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"userId":1}',
            400,
            11,
            "Expected string in 'userId', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyUserId(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"userId":""}',
            400,
            12,
            "Expected value in 'userId', but got \"\" (empty string) in request body."
        );
    }

    public function testNoDataForPropertyUserIdWereFound(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"userId":"00000000-0000-4000-a000-000000000000"}',
            400,
            13,
            "No data found for 'userId' in request body."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsPassword(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"userId":"912ff62e-fef5-442a-9953-b7c18dca9dae"}',
            400,
            10,
            "Missing mandatory property 'password' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyPassword(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"userId":"912ff62e-fef5-442a-9953-b7c18dca9dae","password":1}',
            400,
            11,
            "Expected string in 'password', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyPassword(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"userId":"912ff62e-fef5-442a-9953-b7c18dca9dae","password":""}',
            400,
            12,
            "Expected value in 'password', but got \"\" (empty string) in request body."
        );
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOk(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                '{"userId":"912ff62e-fef5-442a-9953-b7c18dca9dae","password":"new-secret"}'
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail('jane.doe@example.com');
            $this->assertTrue(\password_verify('new-secret', $user->getPassword()->getHash()));
            $this->assertNull($user->getToken());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOkWillCreateNewPassword(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $userId = '912ff62e-fef5-442a-9953-b7c18dca9dae';

        /** @var PasswordSettings $passwordSettings */
        $passwordSettings = $dic->get(PasswordSettingsWithPredefinedValues::class);
        $dic->set(PasswordSettings::class, $passwordSettings);

        $userBeforeResetPassword = $userRepository->getById($userId);
        $this->assertSame(60, \strlen($userBeforeResetPassword->getPassword()->getHash()));
        $this->assertStringStartsWith('$2y$13$', $userBeforeResetPassword->getPassword()->getHash());
        $this->assertSame(PasswordAlgorithms::BCRYPT, $userBeforeResetPassword->getPassword()->getAlgorithm());

        $passwordHashBeforeResetPassword = $userBeforeResetPassword->getPassword()->getHash();

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"userId":"%s","password":"new-secret"}', $userId)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userAfterResetPassword = $userRepository->getById($userId);
            $this->assertSame(96, \strlen($userAfterResetPassword->getPassword()->getHash()));
            $this->assertStringStartsWith('$argon2i$v=19$m=65536,t=4,p=1$', $userAfterResetPassword->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::ARGON2I, $userAfterResetPassword->getPassword()->getAlgorithm());
            $this->assertNotSame($passwordHashBeforeResetPassword, $userAfterResetPassword->getPassword()->getHash());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
