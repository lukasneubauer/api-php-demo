<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Entities\Session;
use App\Entities\Subject;
use App\Repositories\CourseRepository;
use App\Repositories\LessonRepository;
use App\Repositories\SessionRepository;
use App\Repositories\SubjectRepository;
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

final class GetCourseControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'GET';

    /** @var string */
    public const ID = '6fd21fb4-5787-4113-9e48-44ded2492608';

    /** @var string */
    public const ENDPOINT_BASE = '/-/get-course';

    /** @var string */
    public const ENDPOINT = self::ENDPOINT_BASE . '/' . self::ID;

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
     * @dataProvider getMalformedUuids
     */
    public function testMalformedUuidInIdUrlParameter(string $uuid): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT_BASE . '/' . $uuid,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            '',
            400,
            19,
            'Malformed uuid.'
        );
    }

    public static function getMalformedUuids(): array
    {
        return MalformedUuidDataProvider::getMalformedUuids();
    }

    public function testNoDataForUrlParameterCourseIdWereFound(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT_BASE . '/00000000-0000-4000-a000-000000000000',
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            '',
            400,
            7,
            "No data found for 'id' url parameter."
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

        $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

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

            $this->assertArrayHasKey('id', $data);
            $this->assertSame(self::ID, $data['id']);
            $this->assertArrayHasKey('name', $data);
            $this->assertSame('Letní doučování angličtiny', $data['name']);
            $this->assertArrayHasKey('isActive', $data);
            $this->assertTrue($data['isActive']);

            $this->assertArrayHasKey('subject', $data);
            $this->assertIsArray($data['subject']);
            $this->assertArrayHasKey('id', $data['subject']);
            $this->assertTrue(\is_uuid_valid($data['subject']['id']));
            $this->assertArrayHasKey('name', $data['subject']);
            $this->assertSame('Anglický jazyk', $data['subject']['name']);

            $this->assertArrayHasKey('price', $data);
            $this->assertSame(25000, $data['price']);

            $this->assertArrayHasKey('lessons', $data);
            $this->assertIsArray($data['lessons']);
            $this->assertCount(1, $data['lessons']);
            $this->assertArrayHasKey('id', $data['lessons'][0]);
            $this->assertSame('77c76af4-cc32-4695-99cf-41f60b8b7ad3', $data['lessons'][0]['id']);
            $this->assertArrayHasKey('name', $data['lessons'][0]);
            $this->assertSame('Minulý, přítomný a budoucí čas', $data['lessons'][0]['name']);
            $this->assertArrayHasKey('from', $data['lessons'][0]);
            $this->assertSame('2000-01-01 17:00:00', $data['lessons'][0]['from']);
            $this->assertArrayHasKey('to', $data['lessons'][0]);
            $this->assertSame('2000-01-01 19:00:00', $data['lessons'][0]['to']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);
            $course = $courseRepository->getById($data['id']);
            $this->assertInstanceOf(Course::class, $course);
            $this->assertSame($data['id'], $course->getId());
            $this->assertCount(2, $course->getStudents());
            $this->assertCount(1, $course->getLessons());
            $this->assertSame($data['subject']['id'], $course->getSubject()->getId());
            $this->assertSame('8a06562a-c59a-4477-9e0a-ab8b9aba947b', $course->getTeacher()->getId());
            $this->assertSame('Letní doučování angličtiny', $course->getName());
            $this->assertSame(25000, $course->getPrice());
            $this->assertTrue($course->isActive());

            /** @var SubjectRepository $subjectRepository */
            $subjectRepository = $dic->get(SubjectRepository::class);
            $subject = $subjectRepository->getByName('Anglický jazyk');
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame($data['subject']['id'], $subject->getId());
            $this->assertSame('Anglický jazyk', $subject->getName());

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById($data['lessons'][0]['id']);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame($data['lessons'][0]['id'], $lesson->getId());
            $this->assertSame($data['id'], $lesson->getCourse()->getId());
            $this->assertSame('2000-01-01 16:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 18:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
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

        $apiToken = 'wckcmc200gcwsydgw2yy44gvwlaj8dw7zpea0t2abmdma12d217566zq473brmfep5q01lzlxp2pguos';
        $courseId = 'f1f81421-dc6d-4517-9f32-31bdd5c33e0d';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT_BASE . '/' . $courseId,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '73jh28pk2qnvi4pd8lpyrxsab5h6m5v0w04nu2q5',
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

            $this->assertArrayHasKey('id', $data);
            $this->assertSame($courseId, $data['id']);
            $this->assertArrayHasKey('name', $data);
            $this->assertSame('Letní doučování angličtiny', $data['name']);
            $this->assertArrayHasKey('isActive', $data);
            $this->assertTrue($data['isActive']);

            $this->assertArrayHasKey('subject', $data);
            $this->assertIsArray($data['subject']);
            $this->assertArrayHasKey('id', $data['subject']);
            $this->assertTrue(\is_uuid_valid($data['subject']['id']));
            $this->assertArrayHasKey('name', $data['subject']);
            $this->assertSame('Anglický jazyk', $data['subject']['name']);

            $this->assertArrayHasKey('price', $data);
            $this->assertSame(25000, $data['price']);

            $this->assertArrayHasKey('lessons', $data);
            $this->assertIsArray($data['lessons']);
            $this->assertCount(1, $data['lessons']);
            $this->assertArrayHasKey('id', $data['lessons'][0]);
            $this->assertSame('7dbcdb01-e525-42fa-863c-01bbc1bf5dc0', $data['lessons'][0]['id']);
            $this->assertArrayHasKey('name', $data['lessons'][0]);
            $this->assertSame('Minulý, přítomný a budoucí čas', $data['lessons'][0]['name']);
            $this->assertArrayHasKey('from', $data['lessons'][0]);
            $this->assertSame('2000-01-01 17:00:00', $data['lessons'][0]['from']);
            $this->assertArrayHasKey('to', $data['lessons'][0]);
            $this->assertSame('2000-01-01 19:00:00', $data['lessons'][0]['to']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);
            $course = $courseRepository->getById($data['id']);
            $this->assertInstanceOf(Course::class, $course);
            $this->assertSame($data['id'], $course->getId());
            $this->assertCount(1, $course->getStudents());
            $this->assertCount(1, $course->getLessons());
            $this->assertSame($data['subject']['id'], $course->getSubject()->getId());
            $this->assertSame('d2dabfc0-4b60-406a-9961-afb4ec99a18a', $course->getTeacher()->getId());
            $this->assertSame('Letní doučování angličtiny', $course->getName());
            $this->assertSame(25000, $course->getPrice());
            $this->assertTrue($course->isActive());

            /** @var SubjectRepository $subjectRepository */
            $subjectRepository = $dic->get(SubjectRepository::class);
            $subject = $subjectRepository->getByName('Anglický jazyk');
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame($data['subject']['id'], $subject->getId());
            $this->assertSame('Anglický jazyk', $subject->getName());

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById($data['lessons'][0]['id']);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame($data['lessons'][0]['id'], $lesson->getId());
            $this->assertSame($data['id'], $lesson->getCourse()->getId());
            $this->assertSame('2000-01-01 16:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 18:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
