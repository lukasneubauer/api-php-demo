<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181016143410 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE password `password` VARCHAR(255) DEFAULT NULL, CHANGE token `token` VARCHAR(255) DEFAULT NULL, CHANGE security_code `security_code` VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE session CHANGE api_client_id `api_client_id` VARCHAR(255) NOT NULL, CHANGE api_token `api_token` VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `session` CHANGE `api_client_id` api_client_id CHAR(32) NOT NULL COLLATE utf8_unicode_ci, CHANGE `api_token` api_token CHAR(32) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE `user` CHANGE `password` password CHAR(60) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE `token` token CHAR(32) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE `security_code` security_code CHAR(9) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
