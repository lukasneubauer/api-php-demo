<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\Token;
use App\Repositories\UserRepository;
use DateTime;
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

final class RequestEmailToResetPasswordControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/request-email-to-reset-password';

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

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOk(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $emailAddress = 'john.doe@example.com';

        $user = $userRepository->getByEmail($emailAddress);
        $this->assertInstanceOf(Token::class, $user->getToken());
        $tokenBeforeEmailWasSent = $user->getToken();
        $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
        $updatedAtBeforeEmailWasSent = $user->getUpdatedAt();

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"email":"%s"}', $emailAddress)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var Email $emailMessage */
            $emailMessage = self::getMailerMessage(0, 'null://');
            $email = $emailMessage->getHtmlBody();
            $this->assertStringContainsString('<title>Vyžádání nového hesla</title>', $email);

            $user = $userRepository->getByEmail($emailAddress);
            $this->assertInstanceOf(Token::class, $user->getToken());
            $this->assertNotSame($tokenBeforeEmailWasSent->getCode(), $user->getToken()->getCode());
            $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
            $this->assertGreaterThan($updatedAtBeforeEmailWasSent->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
