<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\LogoutPersister;
use App\Repositories\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class LogoutPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testDeleteSession(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var LogoutPersister $logoutPersister */
            $logoutPersister = $dic->get(LogoutPersister::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            EntityManagerCleanup::cleanupEntityManager($dic);

            $logoutPersister->deleteSession();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionFromDatabase = $sessionRepository->getByApiToken($apiToken);

            $this->assertNull($sessionFromDatabase);
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
