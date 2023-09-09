<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Base64\Base64Decoder;
use App\Entities\Session;
use App\Images\ImageFactory;
use App\Repositories\SessionRepository;
use Doctrine\Persistence\Mapping\MappingException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\ErrorResponseTester;
use Tests\HttpMethodsDataProvider;
use Tests\ResponseTester;

final class ProfileControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'GET';

    /** @var string */
    public const ENDPOINT = '/-/profile';

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
    public function testHttpMethodIsNotGet(string $invalidHttpMethod): void
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
            "Usage of incorrect http method '$invalidHttpMethod'. 'GET' was expected."
        );
    }

    public static function getInvalidHttpMethods(): array
    {
        return HttpMethodsDataProvider::getHttpMethodsExcludingGet();
    }

    /**
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOk(): void
    {
        $client = self::createClient();

        $dic = static::getContainer();

        $apiToken = 'etxbqqgheab4geudkycdkpwko6vjsa6eht0rftqgwmbuxp9o3kxaeomabaxsx2vw12cb9e7rp3x7aon9';

        /** @var Base64Decoder $base64Decoder */
        $base64Decoder = $dic->get(Base64Decoder::class);

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
                    'HTTP_Api-Client-Id' => 'g0ksoz1j5232pdadf19f3hcofua14gvyr0kxt66y',
                    'HTTP_Api-Token' => $apiToken,
                ]
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('application/json', $response->headers->get('Content-Type'));
            $apiTokenInResponse = $response->headers->get('Api-Token');
            $this->assertNotNull($apiTokenInResponse);
            $this->assertSame($apiToken, $apiTokenInResponse);
            $data = \json_decode_get_array((string) $response->getContent());
            $this->assertIsArray($data);
            $this->assertArrayNotHasKey('error', $data);

            $this->assertSame('91ebc91a-cbde-4955-93d7-2401916756ba', $data['id']);
            $this->assertSame('Luke', $data['firstName']);
            $this->assertSame('Doe', $data['lastName']);
            $image1 = $imageFactory->createImage($base64Decoder->decode($data['avatar']));
            $this->assertSame(256, $image1->getWidth());
            $this->assertSame(256, $image1->getHeight());
            $this->assertSame('luke.doe@example.com', $data['email']);
            $this->assertTrue($data['isTeacher']);
            $this->assertFalse($data['isStudent']);
            $this->assertSame('Europe/Prague', $data['timezone']);
            $this->assertTrue($data['isActive']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $user = $session->getUser();
            $this->assertSame($data['id'], $user->getId());
            $this->assertSame($data['firstName'], $user->getFirstName());
            $this->assertSame($data['lastName'], $user->getLastName());
            $image2 = $imageFactory->createImage($base64Decoder->decode($user->getAvatar()));
            $this->assertSame(256, $image2->getWidth());
            $this->assertSame(256, $image2->getHeight());
            $this->assertSame($data['email'], $user->getEmail());
            $this->assertSame($data['isTeacher'], $user->isTeacher());
            $this->assertSame($data['isStudent'], $user->isStudent());
            $this->assertSame($data['timezone'], $user->getTimezone());
            $this->assertSame($data['isActive'], $user->isActive());
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

        $apiToken = '7tbcwfw8w095qtt0epo7of6esdtd6j42x7jpnz0mdxqjclhygvyrw4hlmk68oqzpmgu2kiag1mkc5j2c';

        /** @var Base64Decoder $base64Decoder */
        $base64Decoder = $dic->get(Base64Decoder::class);

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
                    'HTTP_Api-Client-Id' => '6cqrfpuq246ef4vokfkr1iycslx4iqeho11rkd4u',
                    'HTTP_Api-Token' => $apiToken,
                ]
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('application/json', $response->headers->get('Content-Type'));
            $apiTokenInResponse = $response->headers->get('Api-Token');
            $this->assertNotNull($apiTokenInResponse);
            $this->assertNotSame($apiToken, $apiTokenInResponse);
            $data = \json_decode_get_array((string) $response->getContent());
            $this->assertIsArray($data);
            $this->assertArrayNotHasKey('error', $data);

            $this->assertSame('82adc992-8a17-4b4a-b84d-c44930c5a023', $data['id']);
            $this->assertSame('Emma', $data['firstName']);
            $this->assertSame('Doe', $data['lastName']);
            $image1 = $imageFactory->createImage($base64Decoder->decode($data['avatar']));
            $this->assertSame(256, $image1->getWidth());
            $this->assertSame(256, $image1->getHeight());
            $this->assertSame('emma.doe@example.com', $data['email']);
            $this->assertTrue($data['isTeacher']);
            $this->assertFalse($data['isStudent']);
            $this->assertSame('Europe/Prague', $data['timezone']);
            $this->assertTrue($data['isActive']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());

            $user = $session->getUser();
            $this->assertSame($data['id'], $user->getId());
            $this->assertSame($data['firstName'], $user->getFirstName());
            $this->assertSame($data['lastName'], $user->getLastName());
            $image2 = $imageFactory->createImage($base64Decoder->decode($user->getAvatar()));
            $this->assertSame(256, $image2->getWidth());
            $this->assertSame(256, $image2->getHeight());
            $this->assertSame($data['email'], $user->getEmail());
            $this->assertSame($data['isTeacher'], $user->isTeacher());
            $this->assertSame($data['isStudent'], $user->isStudent());
            $this->assertSame($data['timezone'], $user->getTimezone());
            $this->assertSame($data['isActive'], $user->isActive());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
