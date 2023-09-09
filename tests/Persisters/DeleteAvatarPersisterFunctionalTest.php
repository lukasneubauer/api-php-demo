<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\DeleteAvatarPersister;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class DeleteAvatarPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testDeleteAvatar(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'a60h3gw3cziv1df090gb8d10c6tc6ahqt7pu6emjrfarl02fmyce11nv9pnhcxc29xkxje4h220855d0';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var DeleteAvatarPersister $deleteAvatarPersister */
            $deleteAvatarPersister = $dic->get(DeleteAvatarPersister::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $session = $sessionRepository->getByApiToken($apiToken);
            $user = $session->getUser();

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $deleteAvatarPersister->deleteAvatar();
            $this->assertNull($userToCheck->getAvatar());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertNull($userFromDatabase->getAvatar());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
