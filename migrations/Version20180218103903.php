<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180218103903 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `session` (`id` CHAR(36) NOT NULL, user_id CHAR(36) DEFAULT NULL, `application_type` INT NOT NULL, `api_client_id` CHAR(40) NOT NULL, `api_token` CHAR(32) NOT NULL, `is_locked` TINYINT(1) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, UNIQUE INDEX UNIQ_D044D5D470CA5872 (`api_token`), INDEX IDX_D044D5D4A76ED395 (user_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `session` ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `session`');
    }
}
