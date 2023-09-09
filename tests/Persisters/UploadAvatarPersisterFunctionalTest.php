<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Base64\Base64Decoder;
use App\Base64\Base64Encoder;
use App\Http\ApiHeaders;
use App\Images\ImageFactory;
use App\Persisters\UploadAvatarPersister;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class UploadAvatarPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testUploadAvatar(): void
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

            /** @var UploadAvatarPersister $uploadAvatarPersister */
            $uploadAvatarPersister = $dic->get(UploadAvatarPersister::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            /** @var Base64Encoder $base64Encoder */
            $base64Encoder = $dic->get(Base64Encoder::class);

            /** @var Base64Decoder $base64Decoder */
            $base64Decoder = $dic->get(Base64Decoder::class);

            /** @var ImageFactory $imageFactory */
            $imageFactory = $dic->get(ImageFactory::class);

            $session = $sessionRepository->getByApiToken($apiToken);
            $user = $session->getUser();

            $sourceString = \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png');
            $base64String = $base64Encoder->encode($sourceString);

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userToCheck = $uploadAvatarPersister->uploadAvatar(['avatar' => $base64String]);
            $iconToCheck = $imageFactory->createImage($base64Decoder->decode($userToCheck->getAvatar()));
            $this->assertSame(256, $iconToCheck->getWidth());
            $this->assertSame(256, $iconToCheck->getHeight());
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $iconFromDatabase = $imageFactory->createImage($base64Decoder->decode($userFromDatabase->getAvatar()));
            $this->assertSame(256, $iconFromDatabase->getWidth());
            $this->assertSame(256, $iconFromDatabase->getHeight());
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
