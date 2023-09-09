<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

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
use Tests\ResponseTester;

final class SecurityCodeConfirmationControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/security-code-confirmation';

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

    public function testMissingMandatoryPropertyInRequestBodyWhichIsEmail(): void
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
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
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
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
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
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
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
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"email":"not-existing-email@example.com"}',
            400,
            13,
            "No data found for 'email' in request body."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsSecurityCode(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"email":"john.doe@example.com"}',
            400,
            10,
            "Missing mandatory property 'securityCode' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertySecurityCode(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"email":"john.doe@example.com","securityCode":1}',
            400,
            11,
            "Expected string in 'securityCode', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertySecurityCode(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"email":"john.doe@example.com","securityCode":""}',
            400,
            12,
            "Expected value in 'securityCode', but got \"\" (empty string) in request body."
        );
    }

    public function testUserDoesHaveAnySecurityCode(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"email":"john.doe@example.com","securityCode":"123456789"}',
            400,
            48,
            'User does not have any security code.'
        );
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testSecurityCodeHasToBeGeneratedAgain(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $emailAddress = 'kate.doe@example.com';

        $userBeforeConfirmation = $userRepository->getByEmail($emailAddress);
        $oldSecurityCode = $userBeforeConfirmation->getSecurityCode();
        $this->assertSame(2, $oldSecurityCode->getInputFailures());
        $this->assertTrue(\strlen($oldSecurityCode->getCode()) === 9);
        $oldSecurityCodeCreatedAt = $oldSecurityCode->getCreatedAt();

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"email":"%s","securityCode":"000000000"}', $emailAddress),
                400,
                44,
                "Incorrect security code has been entered 3 or more times in a row. New security code has been generated and sent on user's email address."
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var Email $emailMessage */
            $emailMessage = self::getMailerMessage(0, 'null://');
            $email = $emailMessage->getHtmlBody();
            $this->assertStringContainsString('<title>Bezpečnostní kód</title>', $email);

            $userAfterConfirmation = $userRepository->getByEmail($emailAddress);
            $newSecurityCode = $userAfterConfirmation->getSecurityCode();
            $this->assertSame(0, $newSecurityCode->getInputFailures());
            $this->assertTrue(\strlen($newSecurityCode->getCode()) === 9);
            $this->assertNotSame($oldSecurityCode->getCode(), $newSecurityCode->getCode());
            $this->assertGreaterThan(
                $oldSecurityCodeCreatedAt->getTimestamp(),
                $newSecurityCode->getCreatedAt()->getTimestamp()
            );
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
    public function testIncorrectSecurityCodeHasBeenEntered(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $emailAddress = 'nina.doe@example.com';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"email":"%s","securityCode":"000000000"}', $emailAddress),
                400,
                43,
                'Incorrect security code has been entered. 2 attempt(s) left.'
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $securityCode = $user->getSecurityCode();
            $this->assertSame(1, $securityCode->getInputFailures());
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
    public function testSecurityCodeHasExpired(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $emailAddress = 'seth.doe@example.com';

        $userBeforeConfirmation = $userRepository->getByEmail($emailAddress);
        $oldSecurityCode = $userBeforeConfirmation->getSecurityCode();
        $this->assertSame(0, $oldSecurityCode->getInputFailures());
        $this->assertTrue(\strlen($oldSecurityCode->getCode()) === 9);
        $oldSecurityCodeCreatedAt = $oldSecurityCode->getCreatedAt();

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"email":"%s","securityCode":"123456789"}', $emailAddress),
                400,
                45,
                "Security code has expired. New security code has been generated and sent on user's email address."
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var Email $emailMessage */
            $emailMessage = self::getMailerMessage(0, 'null://');
            $email = $emailMessage->getHtmlBody();
            $this->assertStringContainsString('<title>Bezpečnostní kód</title>', $email);

            $userAfterConfirmation = $userRepository->getByEmail($emailAddress);
            $newSecurityCode = $userAfterConfirmation->getSecurityCode();
            $this->assertSame(0, $newSecurityCode->getInputFailures());
            $this->assertTrue(\strlen($newSecurityCode->getCode()) === 9);
            $this->assertNotSame($oldSecurityCode->getCode(), $newSecurityCode->getCode());
            $this->assertGreaterThan(
                $oldSecurityCodeCreatedAt->getTimestamp(),
                $newSecurityCode->getCreatedAt()->getTimestamp()
            );
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

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                '{"email":"nina.doe@example.com","securityCode":"123456789"}'
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail('nina.doe@example.com');
            $this->assertNull($user->getSecurityCode());
            $this->assertSame(0, $user->getAuthenticationFailures());
            $this->assertFalse($user->isLocked());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
