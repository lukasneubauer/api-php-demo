<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Base64\Base64Decoder;
use App\Base64\Base64Encoder;
use App\Entities\Password;
use App\Entities\Token;
use App\EntityFactories\UserFactory;
use App\Generators\TokenGenerator;
use App\Images\ImageFactory;
use App\Passwords\PasswordAlgorithms;
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
use Tests\TokenGeneratorWithPredefinedToken;
use Tests\UserFactoryWithPredefinedToken;

final class RegisterControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/register';

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

    public function testMissingMandatoryPropertyInRequestBodyWhichIsFirstName(): void
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
            "Missing mandatory property 'firstName' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyFirstName(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":1}',
            400,
            11,
            "Expected string in 'firstName', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyFirstName(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":""}',
            400,
            12,
            "Expected value in 'firstName', but got \"\" (empty string) in request body."
        );
    }

    public function testStringLengthMustNotBeLongerInRequestBodyForPropertyFirstName(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"%s"}', \str_repeat('a', 256)),
            400,
            55,
            "String length of property 'firstName' must not be longer than 255 characters."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsLastName(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":"John"}',
            400,
            10,
            "Missing mandatory property 'lastName' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyLastName(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":"John","lastName":1}',
            400,
            11,
            "Expected string in 'lastName', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyLastName(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":"John","lastName":""}',
            400,
            12,
            "Expected value in 'lastName', but got \"\" (empty string) in request body."
        );
    }

    public function testStringLengthMustNotBeLongerInRequestBodyForPropertyLastName(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"%s"}', \str_repeat('a', 256)),
            400,
            55,
            "String length of property 'lastName' must not be longer than 255 characters."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsAvatar(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":"John","lastName":"Doe"}',
            400,
            10,
            "Missing mandatory property 'avatar' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyAvatar(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":"John","lastName":"Doe","avatar":1}',
            400,
            11,
            "Expected string or null in 'avatar', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyAvatar(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            '{"firstName":"John","lastName":"Doe","avatar":""}',
            400,
            12,
            "Expected value in 'avatar', but got \"\" (empty string) in request body."
        );
    }

    public function testTryingToUploadAvatarImageOfUnsupportedType(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.xcf');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s"}', $base64String),
            400,
            66,
            'Trying to upload avatar image of unsupported type.'
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsEmail(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s"}', $base64String),
            400,
            10,
            "Missing mandatory property 'email' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyEmail(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":1}', $base64String),
            400,
            11,
            "Expected string in 'email', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyEmail(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":""}', $base64String),
            400,
            12,
            "Expected value in 'email', but got \"\" (empty string) in request body."
        );
    }

    public function testMalformedEmail(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"malformed.email.com"}', $base64String),
            400,
            16,
            'Malformed email.'
        );
    }

    public function testStringLengthMustNotBeLongerInRequestBodyForPropertyEmail(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com%s"}', $base64String, \str_repeat('a', 236)),
            400,
            55,
            "String length of property 'email' must not be longer than 255 characters."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsPassword(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com"}', $base64String),
            400,
            10,
            "Missing mandatory property 'password' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyPassword(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com","password":1}', $base64String),
            400,
            11,
            "Expected string in 'password', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyPassword(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com","password":""}', $base64String),
            400,
            12,
            "Expected value in 'password', but got \"\" (empty string) in request body."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsTimezone(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com","password":"secret"}', $base64String),
            400,
            10,
            "Missing mandatory property 'timezone' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyTimezone(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com","password":"secret","timezone":1}', $base64String),
            400,
            11,
            "Expected string in 'timezone', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyTimezone(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com","password":"secret","timezone":""}', $base64String),
            400,
            12,
            "Expected value in 'timezone', but got \"\" (empty string) in request body."
        );
    }

    public function testSelectedTimezoneIsInvalid(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com","password":"secret","timezone":"XYZ"}', $base64String),
            400,
            57,
            "Selected timezone 'XYZ' is invalid."
        );
    }

    public function testValueIsAlreadyTakenForEmail(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => static::getContainer()->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"john.doe@example.com","password":"secret","timezone":"Europe/Prague"}', $base64String),
            400,
            14,
            "Value for 'email' in request body is already taken."
        );
    }

    public function testCouldNotGenerateUniqueValueForToken(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var TokenGenerator $tokenGenerator */
        $tokenGenerator = $dic->get(TokenGeneratorWithPredefinedToken::class);
        $dic->set(TokenGenerator::class, $tokenGenerator);

        /** @var UserFactory $userFactory */
        $userFactory = $dic->get(UserFactoryWithPredefinedToken::class);
        $dic->set(UserFactory::class, $userFactory);

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
        $base64String = $base64Encoder->encode($sourceString);

        ErrorResponseTester::assertError(
            $client,
            $this,
            self::METHOD,
            self::ENDPOINT,
            ['HTTP_Api-Key' => $dic->getParameter('api_key')],
            \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"extra-new-john-doe@example.com","password":"secret","timezone":"Europe/Prague"}', $base64String),
            400,
            25,
            "Could not generate unique value for 'token' in 5 tries."
        );
    }

    /**
     * @dataProvider getDataForTestOk
     *
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOk(string $fileName): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $emailAddress = 'extra-new-john-doe@example.com';

        /** @var Base64Encoder $base64Encoder */
        $base64Encoder = $dic->get(Base64Encoder::class);

        /** @var Base64Decoder $base64Decoder */
        $base64Decoder = $dic->get(Base64Decoder::class);

        $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/' . $fileName);
        $base64String = $base64Encoder->encode($sourceString);

        /** @var ImageFactory $imageFactory */
        $imageFactory = $dic->get(ImageFactory::class);

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"firstName":"John","lastName":"Doe","avatar":"%s","email":"%s","password":"secret","timezone":"Europe/Prague"}', $base64String, $emailAddress)
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
            $this->assertStringContainsString('<title>Registrace</title>', $email);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertTrue(\is_uuid_valid($user->getId()));
            $this->assertCount(0, $user->getTeacherCourses());
            $this->assertCount(0, $user->getStudentCourses());
            $this->assertCount(0, $user->getSessions());
            $this->assertSame('John', $user->getFirstName());
            $this->assertSame('Doe', $user->getLastName());
            $image = $imageFactory->createImage($base64Decoder->decode($user->getAvatar()));
            $this->assertSame(256, $image->getWidth());
            $this->assertSame(256, $image->getHeight());
            $this->assertSame($emailAddress, $user->getEmail());
            $this->assertInstanceOf(Password::class, $user->getPassword());
            $this->assertTrue(\is_string($user->getPassword()->getHash()));
            $this->assertSame(60, \strlen($user->getPassword()->getHash()));
            $this->assertSame(PasswordAlgorithms::BCRYPT, $user->getPassword()->getAlgorithm());
            $this->assertFalse($user->isTeacher());
            $this->assertFalse($user->isStudent());
            $this->assertSame('Europe/Prague', $user->getTimezone());
            $this->assertInstanceOf(Token::class, $user->getToken());
            $this->assertSame(Token::LENGTH, \strlen($user->getToken()->getCode()));
            $this->assertNull($user->getSecurityCode());
            $this->assertSame(0, $user->getAuthenticationFailures());
            $this->assertFalse($user->isLocked());
            $this->assertFalse($user->isActive());
            $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
            $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
            $this->assertSame($user->getCreatedAt()->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    public static function getDataForTestOk(): array
    {
        return [
            [
                '256x256.gif',
            ],
            [
                '256x256.jpeg',
            ],
            [
                '256x256.jpg',
            ],
            [
                '256x256.png',
            ],
        ];
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOkWithNoAvatar(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $emailAddress = 'extra-new-john-doe@example.com';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                ['HTTP_Api-Key' => $dic->getParameter('api_key')],
                \sprintf('{"firstName":"John","lastName":"Doe","avatar":null,"email":"%s","password":"secret","timezone":"Europe/Prague"}', $emailAddress)
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
            $this->assertStringContainsString('<title>Registrace</title>', $email);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $user = $userRepository->getByEmail($emailAddress);
            $this->assertTrue(\is_uuid_valid($user->getId()));
            $this->assertCount(0, $user->getTeacherCourses());
            $this->assertCount(0, $user->getStudentCourses());
            $this->assertCount(0, $user->getSessions());
            $this->assertSame('John', $user->getFirstName());
            $this->assertSame('Doe', $user->getLastName());
            $this->assertNull($user->getAvatar());
            $this->assertSame($emailAddress, $user->getEmail());
            $this->assertInstanceOf(Password::class, $user->getPassword());
            $this->assertTrue(\is_string($user->getPassword()->getHash()));
            $this->assertSame(60, \strlen($user->getPassword()->getHash()));
            $this->assertSame(PasswordAlgorithms::BCRYPT, $user->getPassword()->getAlgorithm());
            $this->assertFalse($user->isTeacher());
            $this->assertFalse($user->isStudent());
            $this->assertSame('Europe/Prague', $user->getTimezone());
            $this->assertInstanceOf(Token::class, $user->getToken());
            $this->assertSame(Token::LENGTH, \strlen($user->getToken()->getCode()));
            $this->assertNull($user->getSecurityCode());
            $this->assertSame(0, $user->getAuthenticationFailures());
            $this->assertFalse($user->isLocked());
            $this->assertFalse($user->isActive());
            $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
            $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
            $this->assertSame($user->getCreatedAt()->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
