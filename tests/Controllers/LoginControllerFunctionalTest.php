<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\SecurityCode;
use App\Entities\Session;
use App\Entities\User;
use App\EntityFactories\SessionFactory;
use App\Generators\ApiTokenGenerator;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordSettings;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Doctrine\Persistence\Mapping\MappingException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Tests\ApiTokenGeneratorWithPredefinedApiToken;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\ErrorResponseTester;
use Tests\HttpMethodsDataProvider;
use Tests\PasswordSettingsWithPredefinedValues;
use Tests\ResponseTester;
use Tests\SessionFactoryWithPredefinedApiToken;

final class LoginControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/login';

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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
            ],
            '{"email":"john.doe@example.com"}',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
            ],
            '{"email":"john.doe@example.com","password":1}',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
            ],
            '{"email":"john.doe@example.com","password":""}',
            400,
            12,
            "Expected value in 'password', but got \"\" (empty string) in request body."
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

        $emailAddress = 'zack.doe@example.com';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                ],
                \sprintf('{"email":"%s","password":"incorrect-password"}', $emailAddress),
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

        $emailAddress = 'hank.doe@example.com';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                ],
                \sprintf('{"email":"%s","password":"incorrect-password"}', $emailAddress),
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

        $emailAddress = 'lucy.doe@example.com';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                ],
                \sprintf('{"email":"%s","password":"secret"}', $emailAddress),
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

    public function testAttemptToLogIntoAnUnconfirmedUserAccount(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
            ],
            '{"email":"jake.doe@example.com","password":"secret"}',
            400,
            34,
            'Attempt to log into an unconfirmed user account.'
        );
    }

    public function testCouldNotGenerateUniqueValueForApiToken(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var ApiTokenGenerator $apiTokenGenerator */
        $apiTokenGenerator = $dic->get(ApiTokenGeneratorWithPredefinedApiToken::class);
        $dic->set(ApiTokenGenerator::class, $apiTokenGenerator);

        /** @var SessionFactory $sessionFactory */
        $sessionFactory = $dic->get(SessionFactoryWithPredefinedApiToken::class);
        $dic->set(SessionFactory::class, $sessionFactory);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => $dic->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
            ],
            '{"email":"john.doe@example.com","password":"secret"}',
            400,
            25,
            "Could not generate unique value for 'apiToken' in 5 tries."
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

        $apiClientId = 'h22d7prcyns6rd44cloxdj25heni81w9wnbms55g';

        $emailAddress = 'john.doe@example.com';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                ],
                \sprintf('{"email":"%s","password":"secret"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('application/json', $response->headers->get('Content-Type'));
            $data = \json_decode_get_array((string) $response->getContent());
            $this->assertIsArray($data);
            $this->assertArrayNotHasKey('error', $data);
            $this->assertSame(80, \strlen($data['apiToken']));

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($data['apiToken']);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiClientId, $session->getApiClientId());
            $this->assertSame($data['apiToken'], $session->getCurrentApiToken());
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

        $apiClientId = 'h22d7prcyns6rd44cloxdj25heni81w9wnbms55g';

        $emailAddress = 'zack.doe@example.com';

        $userBeforeLogin = $userRepository->getByEmail($emailAddress);
        $this->assertSame(2, $userBeforeLogin->getAuthenticationFailures());

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                ],
                \sprintf('{"email":"%s","password":"secret"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('application/json', $response->headers->get('Content-Type'));
            $data = \json_decode_get_array((string) $response->getContent());
            $this->assertIsArray($data);
            $this->assertArrayNotHasKey('error', $data);
            $this->assertSame(80, \strlen($data['apiToken']));

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userAfterLogin = $userRepository->getByEmail($emailAddress);
            $this->assertSame(0, $userAfterLogin->getAuthenticationFailures());
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

        $apiClientId = 'h22d7prcyns6rd44cloxdj25heni81w9wnbms55g';

        $emailAddress = 'john.doe@example.com';

        /** @var PasswordSettings $passwordSettings */
        $passwordSettings = $dic->get(PasswordSettingsWithPredefinedValues::class);
        $dic->set(PasswordSettings::class, $passwordSettings);

        $userBeforeLogin = $userRepository->getByEmail($emailAddress);
        $this->assertSame(60, \strlen($userBeforeLogin->getPassword()->getHash()));
        $this->assertStringStartsWith('$2y$13$', $userBeforeLogin->getPassword()->getHash());
        $this->assertSame(PasswordAlgorithms::BCRYPT, $userBeforeLogin->getPassword()->getAlgorithm());

        $passwordHashBeforeLogin = $userBeforeLogin->getPassword()->getHash();

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                ],
                \sprintf('{"email":"%s","password":"secret"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('application/json', $response->headers->get('Content-Type'));
            $data = \json_decode_get_array((string) $response->getContent());
            $this->assertIsArray($data);
            $this->assertArrayNotHasKey('error', $data);
            $this->assertSame(80, \strlen($data['apiToken']));

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userAfterLogin = $userRepository->getByEmail($emailAddress);
            $this->assertSame(96, \strlen($userAfterLogin->getPassword()->getHash()));
            $this->assertStringStartsWith('$argon2i$v=19$m=65536,t=4,p=1$', $userAfterLogin->getPassword()->getHash());
            $this->assertSame(PasswordAlgorithms::ARGON2I, $userAfterLogin->getPassword()->getAlgorithm());
            $this->assertNotSame($passwordHashBeforeLogin, $userAfterLogin->getPassword()->getHash());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
