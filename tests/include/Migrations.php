<?php

declare(strict_types=1);

namespace Tests;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Migrations
{
    public static function migrate(ContainerInterface $dic): void
    {
        /** @var DependencyFactory $dependencyFactory */
        $dependencyFactory = $dic->get('doctrine.migrations.dependency_factory');
        $dependencyFactory->getMetadataStorage()->ensureInitialized();
        $migrationPlanCalculator = $dependencyFactory->getMigrationPlanCalculator();
        $version = $dependencyFactory->getVersionAliasResolver()->resolveVersionAlias('latest');
        $plan = $migrationPlanCalculator->getPlanUntilVersion($version);
        $migratorConfiguration = new MigratorConfiguration();
        $migrator = $dependencyFactory->getMigrator();
        $migrator->migrate($plan, $migratorConfiguration);
    }
}
