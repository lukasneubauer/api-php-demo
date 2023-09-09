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
use Tests\MalformedDateTimeDataProvider;
use Tests\MalformedUuidDataProvider;
use Tests\NonsensicalDateTimeDataProvider;
use Tests\ResponseTester;

final class CreateLessonControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'POST';

    /** @var string */
    public const ENDPOINT = '/-/create-lesson';

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

    public function testMissingMandatoryPropertyInRequestBodyWhichIsName(): void
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
            "Missing mandatory property 'name' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyName(): void
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
            '{"name":1}',
            400,
            11,
            "Expected string in 'name', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyName(): void
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
            '{"name":""}',
            400,
            12,
            "Expected value in 'name', but got \"\" (empty string) in request body."
        );
    }

    public function testStringLengthMustNotBeLongerInRequestBodyForPropertyName(): void
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
            \sprintf('{"name":"%s"}', \str_repeat('a', 256)),
            400,
            55,
            "String length of property 'name' must not be longer than 255 characters."
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsFrom(): void
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
            '{"name":"Minulý, přítomný a budoucí čas"}',
            400,
            10,
            "Missing mandatory property 'from' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyFrom(): void
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
            '{"name":"Minulý, přítomný a budoucí čas","from":1}',
            400,
            11,
            "Expected string in 'from', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyFrom(): void
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
            '{"name":"Minulý, přítomný a budoucí čas","from":""}',
            400,
            12,
            "Expected value in 'from', but got \"\" (empty string) in request body."
        );
    }

    /**
     * @dataProvider getMalformedDateTimes
     */
    public function testDateTimeIsMalformedInPropertyFromInRequestBody(string $dateTime): void
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"%s"}', $dateTime),
            400,
            17,
            "Malformed datetime in 'from'. Expected string e.g. '2000-12-24 20:30:00'."
        );
    }

    /**
     * @dataProvider getNonsensicalDateTimes
     */
    public function testGivenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody(string $dateTime): void
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"%s"}', $dateTime),
            400,
            20,
            \sprintf("Given datetime '%s' does not make any sense.", $dateTime)
        );
    }

    public function testMissingMandatoryPropertyInRequestBodyWhichIsTo(): void
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
            '{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00"}',
            400,
            10,
            "Missing mandatory property 'to' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyTo(): void
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
            '{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":1}',
            400,
            11,
            "Expected string in 'to', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyTo(): void
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
            '{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":""}',
            400,
            12,
            "Expected value in 'to', but got \"\" (empty string) in request body."
        );
    }

    /**
     * @dataProvider getMalformedDateTimes
     */
    public function testDateTimeIsMalformedInPropertyToInRequestBody(string $dateTime): void
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"%s"}', $dateTime),
            400,
            17,
            "Malformed datetime in 'to'. Expected string e.g. '2000-12-24 20:30:00'."
        );
    }

    public static function getMalformedDateTimes(): array
    {
        return MalformedDateTimeDataProvider::getMalformedDateTimes();
    }

    /**
     * @dataProvider getNonsensicalDateTimes
     */
    public function testGivenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody(string $dateTime): void
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"%s"}', $dateTime),
            400,
            20,
            \sprintf("Given datetime '%s' does not make any sense.", $dateTime)
        );
    }

    public static function getNonsensicalDateTimes(): array
    {
        return NonsensicalDateTimeDataProvider::getNonsensicalDateTimes();
    }

    /**
     * @dataProvider getDateTimesForComparison
     */
    public function testDateTimeInFromIsGreaterOrEqualToDateTimeInTo(string $dateTimeOne, string $dateTimeTwo): void
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"%s","to":"%s"}', $dateTimeOne, $dateTimeTwo),
            400,
            21,
            "Datetime in 'from' cannot be greater or equal to datetime in 'to'."
        );
    }

    public static function getDateTimesForComparison(): array
    {
        return [
            ['2000-01-01 18:00:00', '2000-01-01 16:00:00'],
            ['2000-01-01 16:00:00', '2000-01-01 16:00:00'],
        ];
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
            '{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"2000-01-01 16:00:00"}',
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
            '{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"2000-01-01 16:00:00","courseId":1}',
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
            '{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"2000-01-01 16:00:00","courseId":""}',
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"2000-01-01 16:00:00","courseId":"%s"}', $uuid),
            400,
            19,
            'Malformed uuid.'
        );
    }

    public static function getMalformedUuids(): array
    {
        return MalformedUuidDataProvider::getMalformedUuids();
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
        $courseId = '6fd21fb4-5787-4113-9e48-44ded2492608';

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
                \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"2000-01-01 16:00:00","courseId":"%s"}', $courseId)
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
            $this->assertTrue(\is_uuid_valid($data['id']));
            $this->assertArrayHasKey('name', $data);
            $this->assertSame('Minulý, přítomný a budoucí čas', $data['name']);
            $this->assertArrayHasKey('from', $data);
            $this->assertSame('2000-01-01 14:00:00', $data['from']);
            $this->assertArrayHasKey('to', $data);
            $this->assertSame('2000-01-01 16:00:00', $data['to']);

            $this->assertArrayHasKey('course', $data);
            $this->assertIsArray($data['course']);
            $this->assertArrayHasKey('id', $data['course']);
            $this->assertSame($courseId, $data['course']['id']);
            $this->assertArrayHasKey('name', $data['course']);
            $this->assertSame('Letní doučování angličtiny', $data['course']['name']);

            $this->assertArrayHasKey('subject', $data['course']);
            $this->assertIsArray($data['course']['subject']);
            $this->assertArrayHasKey('id', $data['course']['subject']);
            $this->assertTrue(\is_uuid_valid($data['course']['subject']['id']));
            $this->assertArrayHasKey('name', $data['course']['subject']);
            $this->assertSame('Anglický jazyk', $data['course']['subject']['name']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById($data['id']);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame($data['id'], $lesson->getId());
            $this->assertSame($data['course']['id'], $lesson->getCourse()->getId());
            $this->assertSame('2000-01-01 13:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 15:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
            $this->assertSame('Minulý, přítomný a budoucí čas', $lesson->getName());

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);
            $course = $courseRepository->getById($data['course']['id']);
            $this->assertInstanceOf(Course::class, $course);
            $this->assertSame($data['course']['id'], $course->getId());
            $this->assertCount(2, $course->getStudents());
            $this->assertCount(2, $course->getLessons());
            $this->assertSame($data['course']['subject']['id'], $course->getSubject()->getId());
            $this->assertSame('8a06562a-c59a-4477-9e0a-ab8b9aba947b', $course->getTeacher()->getId());
            $this->assertSame('Letní doučování angličtiny', $course->getName());
            $this->assertTrue($course->isActive());

            /** @var SubjectRepository $subjectRepository */
            $subjectRepository = $dic->get(SubjectRepository::class);
            $subject = $subjectRepository->getByName('Anglický jazyk');
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame($data['course']['subject']['id'], $subject->getId());
            $this->assertSame('Anglický jazyk', $subject->getName());
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
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '73jh28pk2qnvi4pd8lpyrxsab5h6m5v0w04nu2q5',
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"name":"Minulý, přítomný a budoucí čas","from":"2000-01-01 14:00:00","to":"2000-01-01 16:00:00","courseId":"%s"}', $courseId)
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
            $this->assertTrue(\is_uuid_valid($data['id']));
            $this->assertArrayHasKey('name', $data);
            $this->assertSame('Minulý, přítomný a budoucí čas', $data['name']);
            $this->assertArrayHasKey('from', $data);
            $this->assertSame('2000-01-01 14:00:00', $data['from']);
            $this->assertArrayHasKey('to', $data);
            $this->assertSame('2000-01-01 16:00:00', $data['to']);

            $this->assertArrayHasKey('course', $data);
            $this->assertIsArray($data['course']);
            $this->assertArrayHasKey('id', $data['course']);
            $this->assertSame($courseId, $data['course']['id']);
            $this->assertArrayHasKey('name', $data['course']);
            $this->assertSame('Letní doučování angličtiny', $data['course']['name']);

            $this->assertArrayHasKey('subject', $data['course']);
            $this->assertIsArray($data['course']['subject']);
            $this->assertArrayHasKey('id', $data['course']['subject']);
            $this->assertTrue(\is_uuid_valid($data['course']['subject']['id']));
            $this->assertArrayHasKey('name', $data['course']['subject']);
            $this->assertSame('Anglický jazyk', $data['course']['subject']['name']);

            EntityManagerCleanup::cleanupEntityManager($dic);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiTokenInResponse);
            $this->assertInstanceOf(Session::class, $session);
            $this->assertSame($apiTokenInResponse, $session->getCurrentApiToken());

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById($data['id']);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame($data['id'], $lesson->getId());
            $this->assertSame($data['course']['id'], $lesson->getCourse()->getId());
            $this->assertSame('2000-01-01 13:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 15:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
            $this->assertSame('Minulý, přítomný a budoucí čas', $lesson->getName());

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);
            $course = $courseRepository->getById($data['course']['id']);
            $this->assertInstanceOf(Course::class, $course);
            $this->assertSame($data['course']['id'], $course->getId());
            $this->assertCount(1, $course->getStudents());
            $this->assertCount(2, $course->getLessons());
            $this->assertSame($data['course']['subject']['id'], $course->getSubject()->getId());
            $this->assertSame('d2dabfc0-4b60-406a-9961-afb4ec99a18a', $course->getTeacher()->getId());
            $this->assertSame('Letní doučování angličtiny', $course->getName());
            $this->assertTrue($course->isActive());

            /** @var SubjectRepository $subjectRepository */
            $subjectRepository = $dic->get(SubjectRepository::class);
            $subject = $subjectRepository->getByName('Anglický jazyk');
            $this->assertInstanceOf(Subject::class, $subject);
            $this->assertSame($data['course']['subject']['id'], $subject->getId());
            $this->assertSame('Anglický jazyk', $subject->getName());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
