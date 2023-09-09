<?php

declare(strict_types=1);

namespace Tests\App\Database;

use App\Database\UniqueKey;
use App\Entities\User;
use App\Repositories\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;

/**
 * This test is calling tearDown() method instead of using try..catch..finally because of entity manager locking.
 */
final class UniqueKeyFunctionalTest extends KernelTestCase
{
    public function testExtractUniqueKeyFromExceptionMessage(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        /** @var EntityManager $em */
        $em = $dic->get('doctrine.orm.entity_manager');

        /** @var UserRepository $userRepository */
        $userRepository = $dic->get(UserRepository::class);

        $user = $userRepository->getByEmail('john.doe@example.com');
        $user->setEmail('jake.doe@example.com');

        try {
            $em->persist($user);
            $em->flush();
            $this->fail('Failed to throw exception.');
        } catch (UniqueConstraintViolationException $e) {
            $keyName = (new UniqueKey())->extractUniqueKeyFromExceptionMessage($e->getMessage());
            $this->assertSame(User::UNIQUE_KEY_EMAIL, $keyName);
        }
    }

    protected function tearDown(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        Database::resetDatabase($dic);
    }
}
