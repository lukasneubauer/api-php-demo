<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\Session;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use Doctrine\Persistence\Mapping\MappingException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\ErrorResponseTester;
use Tests\HttpMethodsDataProvider;
use Tests\MalformedUuidDataProvider;
use Tests\ResponseTester;

final class DeleteCourseSubscriptionControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'DELETE';

    /** @var string */
    public const ENDPOINT = '/-/delete-course-subscription';

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
    public function testHttpMethodIsNotDelete(string $invalidHttpMethod): void
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
            "Usage of incorrect http method '$invalidHttpMethod'. 'DELETE' was expected."
        );
    }

    public static function getInvalidHttpMethods(): array
    {
        return HttpMethodsDataProvider::getHttpMethodsExcludingDelete();
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

    public function testMissingMandatoryPropertyInRequestBodyWhichIsCourseId(): void
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
            "Missing mandatory property 'courseId' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyCourseId(): void
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
            '{"courseId":1}',
            400,
            11,
            "Expected string in 'courseId', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyCourseId(): void
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
            '{"courseId":""}',
            400,
            12,
            "Expected value in 'courseId', but got \"\" (empty string) in request body."
        );
    }

    /**
     * @dataProvider getMalformedUuids
     */
    public function testMalformedUuidForPropertyCourseId(string $uuid): void
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
            \sprintf('{"courseId":"%s"}', $uuid),
            400,
            19,
            'Malformed uuid.'
        );
    }

    public static function getMalformedUuids(): array
    {
        return MalformedUuidDataProvider::getMalformedUuids();
    }

    public function testNoDataForPropertyCourseIdWereFound(): void
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
            '{"courseId":"00000000-0000-4000-a000-000000000000"}',
            400,
            13,
            "No data found for 'courseId' in request body."
        );
    }

    public function testCannotUnsubscribeFromOngoingOrEndedCourse(): void
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
            '{"courseId":"04ed0e23-48ec-44f7-8130-5c52666ffa66"}',
            400,
            28,
            'Cannot unsubscribe from ongoing or ended course.'
        );
    }

    public function testCannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '25ndy32v0eqqt2o4uonnrz3pjrkc7g9qn1jt8eln',
                'HTTP_Api-Token' => 'ellbb4woofjai50hut2d2sa1q0yd9rsdq7bdtypvtnuesj64vqhm6rpq8bzfym3sxfa205rx0xrppg34',
            ],
            '{"courseId":"d633abba-9a09-4642-bc27-2ac6698f3463"}',
            400,
            50,
            'Cannot unsubscribe from course to which you are not subscribed to.'
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

        $apiToken = 'jkgc66bbpz1a82fjyxsetm7ztgxd5jbq4l7s5rmsotogayonbjxr7ubqsp5ar93ch6oeji1it03k3494';
        $courseId = 'd633abba-9a09-4642-bc27-2ac6698f3463';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '61peutaw4jrhv1n0jjq68afid6sqxany8fqlid3u',
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"courseId":"%s"}', $courseId)
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

            /** @var SessionRepository $sessionRepo */
            $sessionRepo = $dic->get(SessionRepository::class);
            $session = $sessionRepo->getByApiToken($apiTokenInResponse);

            /** @var CourseRepository $courseRepo */
            $courseRepo = $dic->get(CourseRepository::class);
            $course = $courseRepo->getById($courseId);

            $courseStudents = $course->getStudents();
            $unsubscribedStudent = $session->getUser();
            $this->assertFalse($courseStudents->contains($unsubscribedStudent));
            $this->assertCount(0, $courseStudents);
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

        $apiToken = 'ii2cericjpkeb7xm116jler3hjor9waz4cb6yyx3p115hjoqmcm5brbcftrw1x6oel6cz2si30zkjb2w';
        $courseId = 'ea866a5c-81c1-4112-8978-4787601af1cc';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => 'kb4j81utoing7x5qxvx80dmjkish8n0nzt4amv8x',
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"courseId":"%s"}', $courseId)
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

            /** @var SessionRepository $sessionRepo */
            $sessionRepo = $dic->get(SessionRepository::class);
            $session = $sessionRepo->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());

            /** @var CourseRepository $courseRepo */
            $courseRepo = $dic->get(CourseRepository::class);
            $course = $courseRepo->getById($courseId);

            $courseStudents = $course->getStudents();
            $unsubscribedStudent = $session->getUser();
            $this->assertFalse($courseStudents->contains($unsubscribedStudent));
            $this->assertCount(0, $courseStudents);
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
