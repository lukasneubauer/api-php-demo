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
use Tests\ResponseTester;

final class GetCourseListControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'GET';

    /** @var string */
    public const ENDPOINT = '/-/get-course-list';

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

            $item = $data[0];

            $this->assertArrayHasKey('id', $item);
            $this->assertSame('04ed0e23-48ec-44f7-8130-5c52666ffa66', $item['id']);
            $this->assertArrayHasKey('name', $item);
            $this->assertSame('Letní doučování angličtiny', $item['name']);
            $this->assertArrayHasKey('isActive', $item);
            $this->assertTrue($item['isActive']);

            $this->assertArrayHasKey('subject', $item);
            $this->assertIsArray($item['subject']);
            $this->assertArrayHasKey('id', $item['subject']);
            $this->assertSame('3666055e-aa2f-4dae-b424-145e1a0add4c', $item['subject']['id']);
            $this->assertArrayHasKey('name', $item['subject']);
            $this->assertSame('Anglický jazyk', $item['subject']['name']);

            $this->assertArrayHasKey('price', $item);
            $this->assertSame(25000, $item['price']);

            $this->assertArrayHasKey('lessons', $item);
            $this->assertIsArray($item['lessons']);
            $this->assertCount(1, $item['lessons']);
            $this->assertArrayHasKey('id', $item['lessons'][0]);
            $this->assertSame('123aac3d-a1a3-4261-ad38-5210e38fd7fd', $item['lessons'][0]['id']);
            $this->assertArrayHasKey('name', $item['lessons'][0]);
            $this->assertSame('Minulý, přítomný a budoucí čas', $item['lessons'][0]['name']);
            $this->assertArrayHasKey('from', $item['lessons'][0]);
            $this->assertSame('2000-01-01 15:00:00', $item['lessons'][0]['from']);
            $this->assertArrayHasKey('to', $item['lessons'][0]);
            $this->assertSame('2000-01-01 16:00:00', $item['lessons'][0]['to']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);
            $course = $courseRepository->getById($item['id']);
            $this->assertInstanceOf(Course::class, $course);
            $this->assertSame($item['id'], $course->getId());
            $this->assertCount(1, $course->getStudents());
            $this->assertCount(1, $course->getLessons());
            $this->assertSame($item['subject']['id'], $course->getSubject()->getId());
            $this->assertSame('8a06562a-c59a-4477-9e0a-ab8b9aba947b', $course->getTeacher()->getId());
            $this->assertSame('Letní doučování angličtiny', $course->getName());
            $this->assertSame(25000, $course->getPrice());
            $this->assertTrue($course->isActive());

            /** @var SubjectRepository $subjectRepository */
            $subjectRepository = $dic->get(SubjectRepository::class);
            $subject = $subjectRepository->getByName('Anglický jazyk');
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame($item['subject']['id'], $subject->getId());
            $this->assertSame('Anglický jazyk', $subject->getName());

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById($item['lessons'][0]['id']);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame($item['lessons'][0]['id'], $lesson->getId());
            $this->assertSame($item['id'], $lesson->getCourse()->getId());
            $this->assertSame('2000-01-01 14:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 15:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
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

            $item = $data[0];

            $this->assertArrayHasKey('id', $item);
            $this->assertSame('ea866a5c-81c1-4112-8978-4787601af1cc', $item['id']);
            $this->assertArrayHasKey('name', $item);
            $this->assertSame('Letní doučování angličtiny', $item['name']);
            $this->assertArrayHasKey('isActive', $item);
            $this->assertTrue($item['isActive']);

            $this->assertArrayHasKey('subject', $item);
            $this->assertIsArray($item['subject']);
            $this->assertArrayHasKey('id', $item['subject']);
            $this->assertSame('3666055e-aa2f-4dae-b424-145e1a0add4c', $item['subject']['id']);
            $this->assertArrayHasKey('name', $item['subject']);
            $this->assertSame('Anglický jazyk', $item['subject']['name']);

            $this->assertArrayHasKey('price', $item);
            $this->assertSame(25000, $item['price']);

            $this->assertArrayHasKey('lessons', $item);
            $this->assertIsArray($item['lessons']);
            $this->assertCount(1, $item['lessons']);
            $this->assertArrayHasKey('id', $item['lessons'][0]);
            $this->assertSame('12e525c6-a360-428e-bde8-0269a57ac086', $item['lessons'][0]['id']);
            $this->assertArrayHasKey('name', $item['lessons'][0]);
            $this->assertSame('Minulý, přítomný a budoucí čas', $item['lessons'][0]['name']);
            $this->assertArrayHasKey('from', $item['lessons'][0]);
            $this->assertMatchesRegularExpression('#\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}#', $item['lessons'][0]['from']);
            $this->assertArrayHasKey('to', $item['lessons'][0]);
            $this->assertMatchesRegularExpression('#\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}#', $item['lessons'][0]['to']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);
            $course = $courseRepository->getById($item['id']);
            $this->assertInstanceOf(Course::class, $course);
            $this->assertSame($item['id'], $course->getId());
            $this->assertCount(1, $course->getStudents());
            $this->assertCount(1, $course->getLessons());
            $this->assertSame($item['subject']['id'], $course->getSubject()->getId());
            $this->assertSame('d2dabfc0-4b60-406a-9961-afb4ec99a18a', $course->getTeacher()->getId());
            $this->assertSame('Letní doučování angličtiny', $course->getName());
            $this->assertSame(25000, $course->getPrice());
            $this->assertTrue($course->isActive());

            /** @var SubjectRepository $subjectRepository */
            $subjectRepository = $dic->get(SubjectRepository::class);
            $subject = $subjectRepository->getByName('Anglický jazyk');
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame($item['subject']['id'], $subject->getId());
            $this->assertSame('Anglický jazyk', $subject->getName());

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById($item['lessons'][0]['id']);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame($item['lessons'][0]['id'], $lesson->getId());
            $this->assertSame($item['id'], $lesson->getCourse()->getId());
            $this->assertMatchesRegularExpression('#\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}#', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertMatchesRegularExpression('#\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}#', $lesson->getTo()->format('Y-m-d H:i:s'));
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
