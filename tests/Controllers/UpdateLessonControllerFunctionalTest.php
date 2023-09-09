<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Entities\Session;
use App\Repositories\LessonRepository;
use App\Repositories\SessionRepository;
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

final class UpdateLessonControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'PATCH';

    /** @var string */
    public const ID = '1b2a40de-6772-4b99-9abf-238cd03054c6';

    /** @var string */
    public const ENDPOINT_BASE = '/-/update-lesson';

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
    public function testHttpMethodIsNotPatch(string $invalidHttpMethod): void
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
            "Usage of incorrect http method '$invalidHttpMethod'. 'PATCH' was expected."
        );
    }

    public static function getInvalidHttpMethods(): array
    {
        return HttpMethodsDataProvider::getHttpMethodsExcludingPatch();
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

    public function testNoDataForUrlParameterLessonIdWereFound(): void
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
            '{"name":"Minulý, přítomný a budoucí čas 2"}',
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":1}',
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":""}',
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas 2","from":"%s"}', $dateTime),
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas 2","from":"%s"}', $dateTime),
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00"}',
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":1}',
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":""}',
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"%s"}', $dateTime),
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"%s"}', $dateTime),
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas 2","from":"%s","to":"%s"}', $dateTimeOne, $dateTimeTwo),
            400,
            21,
            "Datetime in 'from' cannot be greater or equal to datetime in 'to'."
        );
    }

    public static function getDateTimesForComparison(): array
    {
        return [
            ['2000-01-01 14:00:00', '2000-01-01 12:00:00'],
            ['2000-01-01 12:00:00', '2000-01-01 12:00:00'],
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00"}',
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":1}',
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":""}',
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
            \sprintf('{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":"%s"}', $uuid),
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
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":"00000000-0000-4000-a000-000000000000"}',
            400,
            13,
            "No data found for 'courseId' in request body."
        );
    }

    public function testTheUserIsTeacherToAcceptThisRequest(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '61peutaw4jrhv1n0jjq68afid6sqxany8fqlid3u',
                'HTTP_Api-Token' => 'jkgc66bbpz1a82fjyxsetm7ztgxd5jbq4l7s5rmsotogayonbjxr7ubqsp5ar93ch6oeji1it03k3494',
            ],
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":"6fd21fb4-5787-4113-9e48-44ded2492608"}',
            400,
            29,
            'To accept this request the user has to be teacher.'
        );
    }

    public function testTheUserIsTeacherToUpdateTheLesson(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1syw3qcxt28ogaouk0t108166s3y98ogzbudw63y',
                'HTTP_Api-Token' => 'e5lmi97882mbwn9it2u9pwkxxmeqi0l3k0mxwwa50l48bum5k9uxql6smzsbjo9r4uhunxzmdgsjh62w',
            ],
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":"6fd21fb4-5787-4113-9e48-44ded2492608"}',
            400,
            51,
            "To update the lesson the user has to be teacher in the given lesson's course."
        );
    }

    public function testCannotUpdateLessonFromOngoingOrEndedCourse(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            \sprintf('%s/%s', self::ENDPOINT_BASE, '123aac3d-a1a3-4261-ad38-5210e38fd7fd'),
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => '1zlfxxa0381wdeogh8amw11xrudalf8rv78wo4tj',
                'HTTP_Api-Token' => 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es',
            ],
            '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":"6fd21fb4-5787-4113-9e48-44ded2492608"}',
            400,
            53,
            'Cannot update lesson from ongoing or ended course.'
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
                ],
                '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":"6fd21fb4-5787-4113-9e48-44ded2492608"}'
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

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById(self::ID);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame('Minulý, přítomný a budoucí čas 2', $lesson->getName());
            $this->assertSame('2000-01-01 09:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 11:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
            $this->assertInstanceOf(Course::class, $lesson->getCourse());
            $this->assertSame('6fd21fb4-5787-4113-9e48-44ded2492608', $lesson->getCourse()->getId());
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
        $lessonId = '12e525c6-a360-428e-bde8-0269a57ac086';

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT_BASE . '/' . $lessonId,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => '73jh28pk2qnvi4pd8lpyrxsab5h6m5v0w04nu2q5',
                    'HTTP_Api-Token' => $apiToken,
                ],
                '{"name":"Minulý, přítomný a budoucí čas 2","from":"2000-01-01 10:00:00","to":"2000-01-01 12:00:00","courseId":"f1f81421-dc6d-4517-9f32-31bdd5c33e0d"}'
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

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);
            $lesson = $lessonRepository->getById($lessonId);
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertSame('Minulý, přítomný a budoucí čas 2', $lesson->getName());
            $this->assertSame('2000-01-01 09:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 11:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
            $this->assertInstanceOf(Course::class, $lesson->getCourse());
            $this->assertSame('f1f81421-dc6d-4517-9f32-31bdd5c33e0d', $lesson->getCourse()->getId());
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
