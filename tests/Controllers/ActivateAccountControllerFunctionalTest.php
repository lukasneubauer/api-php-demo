<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\Token;
use App\Generators\TokenGenerator;
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
use Tests\TokenGeneratorWithPredefinedToken;

final class ActivateAccountControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/activate-account';

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

    public function testMissingMandatoryPropertyInRequestBodyWhichIsToken(): void
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
            "Missing mandatory property 'token' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyToken(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"email":"john.doe@example.com","token":1}',
            400,
            11,
            "Expected string in 'token', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyToken(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"email":"john.doe@example.com","token":""}',
            400,
            12,
            "Expected value in 'token', but got \"\" (empty string) in request body."
        );
    }

    /**
     * @dataProvider getUserEmailCredentials
     */
    public function testUserEmailCredentialsAreCorrect(string $email, string $token, string $property): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"email":"%s","token":"%s"}', $email, $token),
            400,
            13,
            \sprintf("No data found for '%s' in request body.", $property)
        );
    }

    public static function getUserEmailCredentials(): array
    {
        return [
            [
                'not-existing@example.com',
                'incorrect',
                'email',
            ],
            [
                'john.doe@example.com',
                'incorrect',
                'token',
            ],
        ];
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testTokenHasExpired(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $emailAddress = 'john.doe@example.com';
        $expiredToken = 'xvizlczgexbhr404ud0k';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"email":"%s","token":"%s"}', $emailAddress, $expiredToken),
                400,
                18,
                'Token has expired. New email was sent.'
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var Email $emailMessage */
            $emailMessage = self::getMailerMessage(0, 'null://');
            $email = $emailMessage->getHtmlBody();
            $this->assertStringContainsString('<title>Registrace</title>', $email);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertInstanceOf(Token::class, $user->getToken());
            $this->assertNotSame($expiredToken, $user->getToken()->getCode());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    public function testCouldNotGenerateUniqueValueForToken(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var TokenGenerator $tokenGenerator */
        $tokenGenerator = $dic->get(TokenGeneratorWithPredefinedToken::class);
        $dic->set(TokenGenerator::class, $tokenGenerator);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => $dic->getParameter('api_key')],
            '{"email":"nora.doe@example.com","token":"mp9ooi95v4ta71f13a1m"}',
            400,
            25,
            "Could not generate unique value for 'token' in 5 tries."
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
                '{"email":"jake.doe@example.com","token":"a16hup8qryjrfcktg721"}'
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail('jake.doe@example.com');
            $this->assertNull($user->getToken());
            $this->assertTrue($user->isActive());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
