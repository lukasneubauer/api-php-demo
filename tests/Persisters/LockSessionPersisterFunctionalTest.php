<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\LockSessionPersister;
use App\Repositories\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class LockSessionPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testLockSession(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'p1tnyxrxmh1f2egbi0cywuuey64y47a3o0ifuse05dbwrjfm7vrv02yg76519kln5280bdiau7niik9s';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var LockSessionPersister $lockSessionPersister */
            $lockSessionPersister = $dic->get(LockSessionPersister::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $session = $sessionRepository->getByApiToken($apiToken);

            $sessionUpdatedAt = $session->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionToCheck = $lockSessionPersister->lockSession();
            $this->assertTrue($sessionToCheck->isLocked());
            $this->assertGreaterThan(
                $sessionUpdatedAt->getTimestamp(),
                $sessionToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionFromDatabase = $sessionRepository->getByApiToken($apiToken);
            $this->assertTrue($sessionFromDatabase->isLocked());
            $this->assertGreaterThan(
                $sessionUpdatedAt->getTimestamp(),
                $sessionFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
