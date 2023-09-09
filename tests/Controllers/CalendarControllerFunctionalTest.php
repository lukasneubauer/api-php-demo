<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Entities\Session;
use App\Entities\Subject;
use App\Repositories\SessionRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\Mapping\MappingException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Tests\ErrorResponseTester;
use Tests\HttpMethodsDataProvider;
use Tests\ResponseTester;

final class CalendarControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'GET';

    /** @var string */
    public const ENDPOINT = '/-/calendar';

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

        $apiToken = '2iontrtjzgp39ulc64mb743ihccsj9rf6v1y8v0plyc620jc7a670bmc94qvqqo4a3nxw3s2828u5eel';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => 'i2wy9vjahznux6rp1vqs2ik45gex0pffsouja7kt',
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

            $this->assertArrayHasKey('forTeacherHisCourses', $data);
            $this->assertArrayHasKey('forStudentHisLessons', $data);
            $teacherHisCourses = $data['forTeacherHisCourses'];
            $studentHisLessons = $data['forStudentHisLessons'];
            $this->assertIsArray($teacherHisCourses);
            $this->assertIsArray($studentHisLessons);
            $this->assertCount(1, $teacherHisCourses);
            $this->assertCount(1, $studentHisLessons);
            $this->assertSame(
                [
                    'forTeacherHisCourses' => [
                        [
                            'id' => 'e7cb95c5-7b11-44c8-bd6d-1ed894dc6c64',
                            'name' => 'Letní doučování angličtiny',
                            'subject' => [
                                'id' => '3666055e-aa2f-4dae-b424-145e1a0add4c',
                                'name' => 'Anglický jazyk',
                            ],
                            'lessons' => [
                                [
                                    'id' => '66c846b1-983c-4e3c-a477-3dba6f837a53',
                                    'name' => 'Minulý, přítomný a budoucí čas',
                                    'from' => '2000-01-01 17:00:00',
                                    'to' => '2000-01-01 19:00:00',
                                ],
                            ],
                        ],
                    ],
                    'forStudentHisLessons' => [
                        [
                            'id' => '77c76af4-cc32-4695-99cf-41f60b8b7ad3',
                            'name' => 'Minulý, přítomný a budoucí čas',
                            'from' => '2000-01-01 17:00:00',
                            'to' => '2000-01-01 19:00:00',
                            'course' => [
                                'id' => '6fd21fb4-5787-4113-9e48-44ded2492608',
                                'name' => 'Letní doučování angličtiny',
                                'subject' => [
                                    'id' => '3666055e-aa2f-4dae-b424-145e1a0add4c',
                                    'name' => 'Anglický jazyk',
                                ],
                            ],
                        ],
                    ],
                ],
                $data
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $user = $session->getUser();
            $teacherCourses = $user->getTeacherCourses();
            $studentCourses = $user->getStudentCourses();
            $this->assertCount(1, $teacherCourses);
            $this->assertCount(1, $studentCourses);

            /** @var Course $teacherCourse */
            $teacherCourse = $teacherCourses[0];
            $this->assertInstanceOf(Course::class, $teacherCourse);
            $this->assertSame('e7cb95c5-7b11-44c8-bd6d-1ed894dc6c64', $teacherCourse->getId());
            $this->assertSame('Letní doučování angličtiny', $teacherCourse->getName());
            /** @var Subject $teacherCourseSubject */
            $teacherCourseSubject = $teacherCourse->getSubject();
            $this->assertInstanceOf(Subject::class, $teacherCourseSubject);
            $this->assertSame('3666055e-aa2f-4dae-b424-145e1a0add4c', $teacherCourseSubject->getId());
            $this->assertSame('Anglický jazyk', $teacherCourseSubject->getName());
            /** @var Collection $teacherLessons */
            $teacherLessons = $teacherCourse->getLessons();
            $this->assertInstanceOf(Collection::class, $teacherLessons);
            $this->assertCount(1, $teacherLessons);
            /** @var Lesson $teacherLesson */
            $teacherLesson = $teacherLessons[0];
            $this->assertInstanceOf(Lesson::class, $teacherLesson);
            $this->assertSame('66c846b1-983c-4e3c-a477-3dba6f837a53', $teacherLesson->getId());
            $this->assertSame('Minulý, přítomný a budoucí čas', $teacherLesson->getName());
            $this->assertInstanceOf(DateTime::class, $teacherLesson->getFrom());
            $this->assertSame('2000-01-01 16:00:00', $teacherLesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertInstanceOf(DateTime::class, $teacherLesson->getTo());
            $this->assertSame('2000-01-01 18:00:00', $teacherLesson->getTo()->format('Y-m-d H:i:s'));

            /** @var Course $studentCourse */
            $studentCourse = $studentCourses[0];
            $this->assertInstanceOf(Course::class, $studentCourse);
            $this->assertSame('6fd21fb4-5787-4113-9e48-44ded2492608', $studentCourse->getId());
            $this->assertSame('Letní doučování angličtiny', $studentCourse->getName());
            /** @var Subject $studentCourseSubject */
            $studentCourseSubject = $studentCourse->getSubject();
            $this->assertInstanceOf(Subject::class, $studentCourseSubject);
            $this->assertSame('3666055e-aa2f-4dae-b424-145e1a0add4c', $studentCourseSubject->getId());
            $this->assertSame('Anglický jazyk', $studentCourseSubject->getName());
            /** @var Collection $studentLessons */
            $studentLessons = $studentCourse->getLessons();
            $this->assertInstanceOf(Collection::class, $studentLessons);
            $this->assertCount(1, $studentLessons);
            /** @var Lesson $studentLesson */
            $studentLesson = $studentLessons[0];
            $this->assertInstanceOf(Lesson::class, $studentLesson);
            $this->assertSame('77c76af4-cc32-4695-99cf-41f60b8b7ad3', $studentLesson->getId());
            $this->assertSame('Minulý, přítomný a budoucí čas', $studentLesson->getName());
            $this->assertInstanceOf(DateTime::class, $studentLesson->getFrom());
            $this->assertSame('2000-01-01 16:00:00', $studentLesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertInstanceOf(DateTime::class, $studentLesson->getTo());
            $this->assertSame('2000-01-01 18:00:00', $studentLesson->getTo()->format('Y-m-d H:i:s'));
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

        $apiToken = 'hh49ty7bpu8wrygyk2y34xqvmjqgpbm6hobyy20vi4x67ojoehnhtshj3o6hc6v11i76xyacxfwysdxe';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => 'dv02i29q4x1uw9dlcrbtyhms7c7tnyhdeju0zksk',
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

            $this->assertArrayHasKey('forTeacherHisCourses', $data);
            $this->assertArrayHasKey('forStudentHisLessons', $data);
            $teacherCourses = $data['forTeacherHisCourses'];
            $studentLessons = $data['forStudentHisLessons'];
            $this->assertIsArray($teacherCourses);
            $this->assertIsArray($studentLessons);
            $this->assertCount(0, $teacherCourses);
            $this->assertCount(0, $studentLessons);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());

            $user = $session->getUser();
            $this->assertCount(0, $user->getTeacherCourses());
            $this->assertCount(0, $user->getStudentCourses());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
