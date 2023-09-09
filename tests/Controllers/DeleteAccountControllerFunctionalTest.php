<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Entities\Session;
use App\Repositories\CourseRepository;
use App\Repositories\LessonRepository;
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
use Tests\MalformedUuidDataProvider;
use Tests\ResponseTester;

final class DeleteAccountControllerFunctionalTest extends WebTestCase
{
    /** @var string */
    public const METHOD = 'DELETE';

    /** @var string */
    public const ENDPOINT = '/-/delete-account';

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

    public function testMissingMandatoryPropertyInRequestBodyWhichIsId(): void
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
            "Missing mandatory property 'id' in request body."
        );
    }

    public function testDifferentDataTypeInRequestBodyForPropertyId(): void
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
            '{"id":1}',
            400,
            11,
            "Expected string in 'id', but got integer in request body."
        );
    }

    public function testDifferentValueInRequestBodyForPropertyId(): void
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
            '{"id":""}',
            400,
            12,
            "Expected value in 'id', but got \"\" (empty string) in request body."
        );
    }

    /**
     * @dataProvider getMalformedUuids
     */
    public function testMalformedUuidForPropertyId(string $uuid): void
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
            \sprintf('{"id":"%s"}', $uuid),
            400,
            19,
            'Malformed uuid.'
        );
    }

    public static function getMalformedUuids(): array
    {
        return MalformedUuidDataProvider::getMalformedUuids();
    }

    public function testNoDataFoundForPropertyIdInRequestBody(): void
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
            '{"id":"00000000-0000-4000-a000-000000000000"}',
            400,
            13,
            "No data found for 'id' in request body."
        );
    }

    public function testCannotDeleteTeacherWithOngoingCourses(): void
    {
        ErrorResponseTester::assertError(
            static::createClient(),
            $this,
            self::METHOD,
            self::ENDPOINT,
            [
                'HTTP_Api-Key' => static::getContainer()->getParameter('api_key'),
                'HTTP_Api-Client-Id' => 'grcaoctvyeleoaiyoo4wwx62pr50hchwey5rwraj',
                'HTTP_Api-Token' => 'wdcixjplrfmay1cvi78rwtyuhljn3whpuv5p4v595h9k12x15nwd2fczirmgxb4su70n8kl3ilxberyl',
            ],
            '{"id":"8e0c3c5d-dd55-4e59-a192-1731511169f1"}',
            400,
            35,
            'Cannot delete teacher with ongoing courses.'
        );
    }

    public function testCannotDeleteStudentWhichIsSubscribedToOngoingCourses(): void
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
            '{"id":"5cc91071-619a-4f73-ad58-1798b6e17b78"}',
            400,
            36,
            'Cannot delete student which is subscribed to ongoing courses.'
        );
    }

    /**
     * @dataProvider getData
     *
     * @throws MappingException
     * @throws RuntimeException
     */
    public function testOk(
        string $apiClientId,
        string $apiToken,
        string $id,
        bool $isDeleted,
        ?string $courseIdToCheck = null,
        ?string $lessonIdToCheck = null
    ): void {
        $client = self::createClient();

        $dic = static::getContainer();

        /** @var UserRepository $userRepo */
        $userRepo = $dic->get(UserRepository::class);

        $userBeforeDeletion = $userRepo->getById($id);
        $sessions = $userBeforeDeletion->getSessions();
        $this->assertGreaterThan(0, \count($sessions));

        try {
            $client->request(
                self::METHOD,
                self::ENDPOINT,
                [],
                [],
                [
                    'HTTP_Api-Key' => $dic->getParameter('api_key'),
                    'HTTP_Api-Client-Id' => $apiClientId,
                    'HTTP_Api-Token' => $apiToken,
                ],
                \sprintf('{"id":"%s"}', $id)
            );

            /** @var Response $response */
            $response = $client->getResponse();
            ResponseTester::assertResponse(200, $response);
            $this->assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
            $this->assertSame('', $response->getContent());

            EntityManagerCleanup::cleanupEntityManager($dic);

            $user = $userRepo->getById($id);

            /** @var CourseRepository $courseRepo */
            $courseRepo = $dic->get(CourseRepository::class);

            /** @var LessonRepository $lessonRepo */
            $lessonRepo = $dic->get(LessonRepository::class);

            if ($isDeleted) {
                $this->assertNull($user);

                /** @var SessionRepository $sessionRepo */
                $sessionRepo = $dic->get(SessionRepository::class);

                /** @var Session $session */
                foreach ($sessions as $session) {
                    $sess = $sessionRepo->getByApiToken($session->getCurrentApiToken());
                    $this->assertNull($sess);
                }

                if ($courseIdToCheck !== null) {
                    $course = $courseRepo->getById($courseIdToCheck);
                    $this->assertNull($course);
                }

                if ($lessonIdToCheck !== null) {
                    $lesson = $lessonRepo->getById($lessonIdToCheck);
                    $this->assertNull($lesson);
                }
            } else {
                $this->assertNotNull($user);
                $this->assertTrue($user->isEmpty());
                $this->assertCount(0, $user->getSessions());

                if ($courseIdToCheck !== null) {
                    $course = $courseRepo->getById($courseIdToCheck);
                    $this->assertNotNull($course);
                }

                if ($lessonIdToCheck !== null) {
                    $lesson = $lessonRepo->getById($lessonIdToCheck);
                    $this->assertNotNull($lesson);
                }
            }
        } catch (RuntimeException $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    public static function getData(): array
    {
        return [
            [
                '6uev2q6avn4br3thqay0xiuy0qkqhiu91jisih8e',
                'rfdthtk69alj8wz2usvp610rp4qbp51zxt4b87g1y5x7ogtf2epcosz6flj12t4sluitplji7ivaq09m', // user who is teacher but has no courses
                '7daef828-ae8f-4be7-b71f-385eca118008',
                true,
            ],
            [
                'jpv0t5vl1jdjcw61jgch3u1f7krtpm0fnypbqi5j',
                '8z51qpr0lpovoiqcl9r8cgfzrkao2mknal7t8wsbbea2zsvhgw9dopcviq0insiamvxsfrxjon1vx772', // user who is teacher who has one course but it is not active
                '01c1c6dc-b3ac-494a-bb52-571540c87305',
                true,
                'f8b4d448-72dd-432f-a2a9-6256ba75636d',
            ],
            [
                '73wqcb3vsi5792xrlail5n9eiak79ibp60s8zjhu',
                'zltcgvejzv8h70xrcho5clp0f3lypcyertjiasc1wme40aw89weflt53ckpuntmar49sr5q479rcs36j', // user who is teacher who has one course which is active but it has no students
                'bed66669-ca72-459a-9f27-4e1132b4dd08',
                true,
                '7ec8d249-21ca-4468-9bc3-820bcb70418d',
            ],
            [
                'x02l8n5isi8tlyyy16my4qo0dsyf4f1i0t95kjyf',
                'f5hdxawsd4db79qkk2479i0pep6c7bzohzi6obzzqd4lj6r5jbqe75g4kzd6ug0wh34tjarykgkko6vz', // user who is teacher who has one course which is active and it has one student but it is in the past
                'fb591a7b-83d7-4170-a795-88307f11123e',
                false,
                '06c7e6ef-2232-4445-abf6-f98d529c7c5f',
                'bdf264e5-1a17-4880-9688-7783e143a498',
            ],
            [
                'w9cn2fqnt8h64qbdhoza30nqou43wmkr855js7ld',
                'naa1zti71s61335lz4bvy7mw1m7kty89m52ollx1stfx848jpbm2e53l6dw7igtzbs0gi79tt0n3lbvp', // user who is teacher who has one course which is active and it has one student but it is in the future
                'ec6506e9-764e-4243-8299-c53b3615cff8',
                true,
                'd49d4258-44e4-4cce-b5cb-29dc85a16d07',
                '0917142b-047e-4b45-a357-035495dd231f',
            ],
            [
                'ekzd8sm44ibpmvekvei8d741l2gm5gvdkef8wsu1',
                'c28xp6s6e13tnh5g7vjt8daq6c0xlxfz18b0gdul6clap4x24lcefnnzg25nwmbg6phpne16vpi6pzgs', // user who is student who is not subscribed to any course
                '3f150ae6-ece6-410a-84ca-754298d1a20d',
                true,
            ],
            [
                'q0zepgho6ql4921x8w6faaozh6aowtrikrodunbb',
                'r22bx7zmljo34yis0m5tu2zrlqfl6ufh7tik82708c2u85zdj4zd3jrtwlpq238p6ee7pnd84s7i1pnu', // user who is student who is subscribed to one course which is in the past
                'adfdcdfe-a77b-4b01-b530-93a0de4e1cd8',
                false,
            ],
            [
                'vrb0gthr54m596hkxnaae56cazx0l33i72zrinq1',
                'omempcv0vtq07h5kgdumcbb82wo0l4v99z64xy5rzbiazgr0wvny6zjfv01jgowhxgmo4s6rom9la1wa', // user who is student who is subscribed to one course which is in the future
                '81ea4300-835c-4453-9197-e80dc0276adc',
                true,
            ],
        ];
    }
}
