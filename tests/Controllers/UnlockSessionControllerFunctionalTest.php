<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\SecurityCode;
use App\Entities\Session;
use App\Entities\User;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordSettings;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Doctrine\Persistence\Mapping\MappingException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\ErrorResponseTester;
use Tests\HttpMethodsDataProvider;
use Tests\PasswordSettingsWithPredefinedValues;
use Tests\ResponseTester;

final class UnlockSessionControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/unlock-session';

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

    public function testApiClientIdIsMissing(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '',
            400,
            1,
            "Missing mandatory 'Api-Client-Id' http header."
        );
    }

    public function testApiClientIdIsEmpty(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '',
            ],
            '',
            400,
            2,
            "Missing value for 'Api-Client-Id' http header."
        );
    }

    public function testApiTokenIsMissing(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
            ],
            '',
            400,
            1,
            "Missing mandatory 'Api-Token' http header."
        );
    }

    public function testApiTokenIsEmpty(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '',
            ],
            '',
            400,
            2,
            "Missing value for 'Api-Token' http header."
        );
    }

    public function testApiTokenIsInvalid(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => 'xyz',
            ],
            '',
            400,
            3,
            "Invalid value for 'Api-Token' http header."
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
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
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
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
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
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{',
            400,
            9,
            'Malformed JSON in request body.'
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsEmail(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{}',
            400,
            10,
            "Missing mandatory property 'email' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyEmail(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":1}',
            400,
            11,
            "Expected string in 'email', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyEmail(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":""}',
            400,
            12,
            "Expected value in 'email', but got \"\" (empty string) in request body."
        );
    }

    public function testMalformedEmail(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"malformed.email.com"}',
            400,
            16,
            'Malformed email.'
        );
    }

    public function testNoDataFoundForPropertyEmailInRequestBody(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"not-existing-email@example.com"}',
            400,
            13,
            "No data found for 'email' in request body."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsPassword(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"wade.doe@example.com"}',
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
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"wade.doe@example.com","password":1}',
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
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"wade.doe@example.com","password":""}',
            400,
            12,
            "Expected value in 'password', but got \"\" (empty string) in request body."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsOldApiClientId(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"wade.doe@example.com","password":"secret"}',
            400,
            10,
            "Missing mandatory property 'oldApiClientId' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyOldApiClientId(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"wade.doe@example.com","password":"secret","oldApiClientId":1}',
            400,
            11,
            "Expected string in 'oldApiClientId', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyOldApiClientId(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"wade.doe@example.com","password":"secret","oldApiClientId":""}',
            400,
            12,
            "Expected value in 'oldApiClientId', but got \"\" (empty string) in request body."
        );
    }

    public function testUserIsTryingToUseAnotherEmailAddress(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"john.doe@example.com","password":"secret","oldApiClientId":"kbdd1lwf089776ako05mtyfo2u44ok3dw0jisvzk"}',
            400,
            46,
            "Re-authentication failed. User is trying to use another user's email address."
        );
    }

    public function testOldApiClientIdIsDifferentThanTheOneInCurrentSession(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '{"email":"wade.doe@example.com","password":"secret","oldApiClientId":"0000000000000000000000000000000000000000"}',
            400,
            47,
            'Re-authentication failed. Value of old api client id in request body is different than the one in current session.'
        );
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testAccountHasBeenLocked(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $emailAddress = 'maya.doe@example.com';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                    'HTTP_Api-Token' => 's1q2f6dve71k0uuwxfh9xmgw58paza9tbzuvf64jn16m4mfln9fps2m2ho6fj5mdpx3ojrf9cd4957wy',
                ],
                \sprintf('{"email":"%s","password":"incorrect-password","oldApiClientId":"m9l08j5z2quqe83ctaleom7f3dvas07jj9t6hwy1"}', $emailAddress),
                400,
                41,
                'Incorrect password has been entered 3 or more times in a row. Account has been locked for security reasons.'
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertSame(3, $user->getAuthenticationFailures());
            $this->assertTrue($user->isLocked());
            $this->assertNull($user->getSecurityCode());
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
    public function testIncorrectPasswordHasBeenEntered(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $emailAddress = 'anna.doe@example.com';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                    'HTTP_Api-Token' => 'xkwz5uijlbxu02joisif9lnd6os6x05b7di8dljwyvu8u9ouy58fiw8z1imngr87weoc0xpa14blaztx',
                ],
                \sprintf('{"email":"%s","password":"incorrect-password","oldApiClientId":"rukkhxoe154kroh8s3vncoz962i61npt2lvy1al1"}', $emailAddress),
                400,
                40,
                'Incorrect password has been entered. 2 attempt(s) left.'
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertSame(1, $user->getAuthenticationFailures());
            $this->assertFalse($user->isLocked());
            $this->assertNull($user->getSecurityCode());
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
    public function testSecurityCodeHasToBeGenerated(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $emailAddress = 'zora.doe@example.com';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2',
                    'HTTP_Api-Token' => 'k1i08hm442u63xc32490rzp0nwc7r4p4xmgamm8882eh7aoq073qb64njxung9y9rqc9psz4fhdjt032',
                ],
                \sprintf('{"email":"%s","password":"secret","oldApiClientId":"zl4t979kxk5zqs6kr7ql6kk5baoeq2aacy1f9r3m"}', $emailAddress),
                400,
                42,
                "User's authentication was successful, but since there were 3 or more failed login attempts in a row in the past, a security code has been generated and sent on user's email address."
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var Email $emailMessage */
            $emailMessage = self::getMailerMessage(0, 'null://');
            $email = $emailMessage->getHtmlBody();
            $this->assertStringContainsString('<title>Bezpečnostní kód</title>', $email);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertSame(3, $user->getAuthenticationFailures());
            $this->assertTrue($user->isLocked());
            $this->assertInstanceOf(SecurityCode::class, $user->getSecurityCode());
            $this->assertTrue(\strlen($user->getSecurityCode()->getCode()) === 9);
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
    public function testOk(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $apiClientId = 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2';
        $apiToken = '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh';

        $emailAddress = 'wade.doe@example.com';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"email":"%s","password":"secret","oldApiClientId":"kbdd1lwf089776ako05mtyfo2u44ok3dw0jisvzk"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $apiTokenInResponse = $response->headers->get('Api-Token');
            $this->assertNotNull($apiTokenInResponse);
            $this->assertSame($apiToken, $apiTokenInResponse);
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiClientId, $session->getApiClientId());
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());
            $this->assertFalse($session->isLocked());

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertInstanceOf(User::class, $user);
            $this->assertSame($session->getUser()->getId(), $user->getId());
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
    public function testOkWithExpiredApiToken(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $apiClientId = 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2';
        $apiToken = 'gidb7tko6d734wn6dljn2xjkb63ijwur8z15falxk49p2jq3v7hd2erziw86dokgzxt1r9s4b9rzuzif';

        $emailAddress = 'kirk.doe@example.com';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"email":"%s","password":"secret","oldApiClientId":"ba4xw6rv3n9eqlf9rbrvauf3s2xhbug52rvu33vi"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $apiTokenInResponse = $response->headers->get('Api-Token');
            $this->assertNotNull($apiTokenInResponse);
            $this->assertNotSame($apiToken, $apiTokenInResponse);
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiClientId, $session->getApiClientId());
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());
            $this->assertFalse($session->isLocked());

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertInstanceOf(User::class, $user);
            $this->assertSame($session->getUser()->getId(), $user->getId());
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
    public function testOkWillResetAuthenticationFailures(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $apiClientId = 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2';
        $apiToken = 's1q2f6dve71k0uuwxfh9xmgw58paza9tbzuvf64jn16m4mfln9fps2m2ho6fj5mdpx3ojrf9cd4957wy';

        $emailAddress = 'maya.doe@example.com';

        $userBeforeUnlockedSession = $userRepository->getByEmail($emailAddress);
        $this->assertSame(2, $userBeforeUnlockedSession->getAuthenticationFailures());

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"email":"%s","password":"secret","oldApiClientId":"m9l08j5z2quqe83ctaleom7f3dvas07jj9t6hwy1"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $apiTokenInResponse = $response->headers->get('Api-Token');
            $this->assertNotNull($apiTokenInResponse);
            $this->assertSame($apiToken, $apiTokenInResponse);
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userAfterUnlockedSession = $userRepository->getByEmail($emailAddress);
            $this->assertSame(0, $userAfterUnlockedSession->getAuthenticationFailures());
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
    public function testOkWillRehashPassword(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $apiClientId = 'v7ltzna9zwcr4xfw6si0nkuevv68jvmh9b8p0dw2';
        $apiToken = '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh';

        $emailAddress = 'wade.doe@example.com';

        /** @var PasswordSettings $passwordSettings */
        $passwordSettings = $dic->get(PasswordSettingsWithPredefinedValues::class);
        $dic->set(PasswordSettings::class, $passwordSettings);

        $userBeforeUnlockedSession = $userRepository->getByEmail($emailAddress);
        $this->assertSame(60, \strlen($userBeforeUnlockedSession->getPassword()->getHash()));
        $this->assertStringStartsWith('$2y$13$', $userBeforeUnlockedSession->getPassword()->getHash());
        $this->assertSame(PasswordAlgorithms::BCRYPT, $userBeforeUnlockedSession->getPassword()->getAlgorithm());

        $passwordHashBeforeUnlockedSession = $userBeforeUnlockedSession->getPassword()->getHash();

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"email":"%s","password":"secret","oldApiClientId":"kbdd1lwf089776ako05mtyfo2u44ok3dw0jisvzk"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $apiTokenInResponse = $response->headers->get('Api-Token');
            $this->assertNotNull($apiTokenInResponse);
            $this->assertSame($apiToken, $apiTokenInResponse);
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userAfterUnlockedSession = $userRepository->getByEmail($emailAddress);
            $this->assertSame(96, \strlen($userAfterUnlockedSession->getPassword()->getHash()));
            $this->assertStringStartsWith('$argon2i$v=19$m=65536,t=4,p=1$', $userAfterUnlockedSession->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::ARGON2I, $userAfterUnlockedSession->getPassword()->getAlgorithm());
            $this->assertNotSame($passwordHashBeforeUnlockedSession, $userAfterUnlockedSession->getPassword()->getHash());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
