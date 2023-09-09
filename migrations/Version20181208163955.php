<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181208163955 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sessions ADD `old_api_token` VARCHAR(255) DEFAULT NULL AFTER `api_client_id`, ADD `old_api_token_created_at` DATETIME DEFAULT NULL AFTER `old_api_token`, ADD `current_api_token` VARCHAR(255) NOT NULL AFTER `old_api_token_created_at`, ADD `current_api_token_created_at` DATETIME NOT NULL AFTER `current_api_token`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A609D1350661F4D ON sessions (`old_api_token`)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A609D13A13742BC ON sessions (`current_api_token`)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_9A609D1350661F4D ON `sessions`');
        $this->addSql('DROP INDEX UNIQ_9A609D13A13742BC ON `sessions`');
        $this->addSql('ALTER TABLE `sessions` DROP `old_api_token`, DROP `old_api_token_created_at`, DROP `current_api_token`, DROP `current_api_token_created_at`');
    }
}
