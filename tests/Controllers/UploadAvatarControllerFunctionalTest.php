<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Base64\Base64Decoder;
use App\Base64\Base64Encoder;
use App\Entities\Session;
use App\Images\ImageFactory;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Doctrine\Persistence\Mapping\MappingException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\ErrorResponseTester;
use Tests\HttpMethodsDataProvider;
use Tests\ResponseTester;

final class UploadAvatarControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/upload-avatar';

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

    public function testApiClientIdIsInvalid(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'CLIENT-ID',
            ],
            '',
            400,
            3,
            "Invalid value for 'Api-Client-Id' http header."
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
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
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'xyz',
            ],
            '',
            400,
            3,
            "Invalid value for 'Api-Token' http header."
        );
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testSessionFoundByApiTokenButItsClientIdDoesNotMatch(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $apiClientId = 'zqf3gr988yzgkwmdpdwq1zagqolrqsv7op8u7npr';
        $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

        try {
            ErrorResponseTester::assertError(
                $client,
                $this,
                self::METHOD,
                self::ENDPOINT,
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                    'HTTP_Api-Token' => $apiToken,
                ],
                '',
                400,
                38,
                'Session found by api token but its client id does not match with the one provided in header Api-Client-Id. Session has been locked for security reasons.'
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiToken);
            $this->assertTrue($session->isLocked());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    public function testSessionIsLocked(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'kbdd1lwf089776ako05mtyfo2u44ok3dw0jisvzk',
                'HTTP_Api-Token' => '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh',
            ],
            '',
            400,
            39,
            'Session is locked. User must re-authenticate.'
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
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
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
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
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
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            '{',
            400,
            9,
            'Malformed JSON in request body.'
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsAvatar(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            '{}',
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
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            '{"avatar":1}',
            400,
            11,
            "Expected string in 'avatar', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyAvatar(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            '{"avatar":""}',
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
            [
                'HTTP_Api-Key' => $dic->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            \sprintf('{"avatar":"%s"}', $base64String),
            400,
            66,
            'Trying to upload avatar image of unsupported type.'
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

        $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

        /** @var SessionRepository $sessionRepository */
        $sessionRepository = $dic->get(SessionRepository::class);

        $session = $sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $this->assertNull($user->getAvatar());

        $userUpdatedAt = $user->getUpdatedAt();

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
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"avatar":"%s"}', $base64String)
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

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $userToCheck = $userRepository->getById($user->getId());

            $image = $imageFactory->createImage($base64Decoder->decode($userToCheck->getAvatar()));
            $this->assertSame(256, $image->getWidth());
            $this->assertSame(256, $image->getHeight());
            $this->assertGreaterThan($userUpdatedAt->getTimestamp(), $userToCheck->getUpdatedAt()->getTimestamp());
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
     * @dataProvider getDataForTestOkWithExpiredApiToken
     *
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOkWithExpiredApiToken(string $fileName): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $apiToken = 'wckcmc200gcwsydgw2yy44gvwlaj8dw7zpea0t2abmdma12d217566zq473brmfep5q01lzlxp2pguos';

        /** @var SessionRepository $sessionRepository */
        $sessionRepository = $dic->get(SessionRepository::class);

        $session = $sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $this->assertNull($user->getAvatar());

        $userUpdatedAt = $user->getUpdatedAt();

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
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '73jh28pk2qnvi4pd8lpyrxsab5h6m5v0w04nu2q5',
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"avatar":"%s"}', $base64String)
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
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);
            $userToCheck = $userRepository->getById($user->getId());

            $image = $imageFactory->createImage($base64Decoder->decode($userToCheck->getAvatar()));
            $this->assertSame(256, $image->getWidth());
            $this->assertSame(256, $image->getHeight());
            $this->assertGreaterThan($userUpdatedAt->getTimestamp(), $userToCheck->getUpdatedAt()->getTimestamp());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    public static function getDataForTestOkWithExpiredApiToken(): array
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
}
