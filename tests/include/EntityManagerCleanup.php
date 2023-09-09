<?php

declare(strict_types=1);

namespace Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EntityManagerCleanup
{
    /**
     * @throws MappingException
     */
    public static function cleanupEntityManager(ContainerInterface $dic): void
    {
        /** @var EntityManager $em */
        $em = $dic->get('doctrine.orm.entity_manager');
        $em->clear();
    }
}
