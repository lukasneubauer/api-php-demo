<?php

declare(strict_types=1);

namespace Tests;

use Doctrine\ORM\EntityManager;
use Nelmio\Alice\Loader\NativeLoader as FixturesLoader;

final class Fixtures
{
    public static function load(EntityManager $em): void
    {
        $loader = new FixturesLoader();
        $objectSet = $loader->loadFile(__DIR__ . '/../../fixtures/fixtures-for-phpunit.php');

        foreach ($objectSet->getObjects() as $object) {
            $em->persist($object);
        }

        $em->flush();
    }
}
