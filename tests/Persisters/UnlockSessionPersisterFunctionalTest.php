<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Passwords\PasswordAlgorithms;
use App\Persisters\UnlockSessionPersister;
use App\Repositories\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class UnlockSessionPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testUnlockSession(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiClientId = 'CLIENT-ID';
            $apiToken = '6ejocghxhtueedkvn1s5dx8cxtb59g21i87x3bjngv88azujmtoy7xsum60lzp4bq24q3fogrijyhalh';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_CLIENT_ID, $apiClientId);
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var UnlockSessionPersister $unlockSessionPersister */
            $unlockSessionPersister = $dic->get(UnlockSessionPersister::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $session = $sessionRepository->getByApiToken($apiToken);

            $sessionUpdatedAt = $session->getUpdatedAt();

            $user = $session->getUser();

            $password = $user->getPassword();

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionToCheck = $unlockSessionPersister->unlockSession(['password' => 'secret']);

            $this->assertSame($apiClientId, $sessionToCheck->getApiClientId());
            $this->assertFalse($sessionToCheck->isLocked());
            $this->assertGreaterThan(
                $sessionUpdatedAt->getTimestamp(),
                $sessionToCheck->getUpdatedAt()->getTimestamp()
            );

            $userToCheck = $sessionToCheck->getUser();

            $this->assertSame(0, $userToCheck->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            $passwordToCheck = $userToCheck->getPassword();

            $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordToCheck->getAlgorithm());
            $this->assertSame($password->getHash(), $passwordToCheck->getHash());

            $this->assertSame(
                $sessionToCheck->getUpdatedAt()->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $sessionFromDatabase = $sessionRepository->getByApiToken($sessionToCheck->getCurrentApiToken());

            $this->assertSame($apiClientId, $sessionFromDatabase->getApiClientId());
            $this->assertFalse($sessionFromDatabase->isLocked());
            $this->assertGreaterThan(
                $sessionUpdatedAt->getTimestamp(),
                $sessionFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $userFromDatabase = $sessionFromDatabase->getUser();

            $this->assertSame(0, $userFromDatabase->getAuthenticationFailures());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $passwordFromDatabase = $userFromDatabase->getPassword();

            $this->assertSame(PasswordAlgorithms::BCRYPT, $passwordFromDatabase->getAlgorithm());
            $this->assertSame($password->getHash(), $passwordFromDatabase->getHash());

            $this->assertSame(
                $sessionFromDatabase->getUpdatedAt()->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
