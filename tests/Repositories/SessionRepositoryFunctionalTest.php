<?php

declare(strict_types=1);

namespace Tests\App\Repositories;

use App\Entities\Session;
use App\Repositories\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Throwable;

class SessionRepositoryFunctionalTest extends KernelTestCase
{
    /**
     * @dataProvider getApiTokensForTestThatReturnsSession
     *
     * @throws Throwable
     */
    public function testGetByApiTokenReturnsSession(string $apiToken): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiToken);
            $this->assertInstanceOf(Session::class, $session);
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    public static function getApiTokensForTestThatReturnsSession(): array
    {
        return [
            ['pcsd5f6tnpttfeq26h1yba7q7qsj0pvedksvmf0bdui7x09pgn243f8pdfu6uhwgawomacs53ekwe0q5'], // Non expired current api token
            ['fr0s9lbliztquqdk54gus9x2zjit33qxj9jlnjl4arbe2bbmmm4ynbh5c2oc5xxti8pwgye3jdxajstu'], // Non expired old api token
            ['gu0ahm3v8jpqaf5805o39kf76488noqngmi2qak47doaq60l6nxjrv6zjid0imu633e9inol19qdt4mw'], // Expired current api token
        ];
    }

    /**
     * @dataProvider getApiTokensForTestThatReturnsNull
     *
     * @throws Throwable
     */
    public function testGetByApiTokenReturnsNull(string $apiToken): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);
            $session = $sessionRepository->getByApiToken($apiToken);
            $this->assertNull($session);
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    public static function getApiTokensForTestThatReturnsNull(): array
    {
        return [
            ['g7vs3hqda7mybwfanur5fewd6w707m6becoxgqer7lcv9l7bykbf1ure8axygiham8tc6r26ctt821oo'], // Expired old api token
            ['fvrfm3xzl24ge9ieng663tydun6hoaa6dn7zdbxtanucphyv1yz8jc5lr7zj84vn7sur2lq0n0unt4w4'], // Non existent current api token
            ['yhlwpxt4zhpmpukcy9u2icmjkoglhm6fkt5jzxjb4gmzuhltwqpfbpbzj64crn3tvsymh66ps4w1fbp1'], // Non existent old api token
        ];
    }
}
