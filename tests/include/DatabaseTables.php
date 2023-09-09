<?php

declare(strict_types=1);

namespace Tests;

use Doctrine\DBAL\Schema\AbstractSchemaManager;

final class DatabaseTables
{
    public static function dropTables(AbstractSchemaManager $schemaManager): void
    {
        $schemaManager->dropTable('`subscriptions`');
        $schemaManager->dropTable('`payments`');
        $schemaManager->dropTable('`lessons`');
        $schemaManager->dropTable('`courses`');
        $schemaManager->dropTable('`sessions`');
        $schemaManager->dropTable('`subjects`');
        $schemaManager->dropTable('`users`');
        $schemaManager->dropTable('`migrations`');
    }
}
