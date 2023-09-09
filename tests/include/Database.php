<?php

declare(strict_types=1);

namespace Tests;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Database
{
    public static function resetDatabase(ContainerInterface $dic): void
    {
        /** @var EntityManager $em */
        $em = $dic->get('doctrine.orm.entity_manager');
        $conn = $em->getConnection();
        DatabaseTables::dropTables($conn->getSchemaManager());
        Migrations::migrate($dic);
        Fixtures::load($em);
    }
}
