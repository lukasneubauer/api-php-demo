<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180422105549 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD `security_code` CHAR(9) DEFAULT NULL AFTER `token_created_at`, ADD `security_code_created_at` DATETIME DEFAULT NULL AFTER `security_code`, ADD `security_code_failures` INT NOT NULL AFTER `security_code_created_at`, ADD `login_failures` INT NOT NULL AFTER `security_code_failures`, ADD `is_locked` TINYINT(1) NOT NULL AFTER `login_failures`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C9F95CFA ON user (`security_code`)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_8D93D649C9F95CFA ON `user`');
        $this->addSql('ALTER TABLE `user` DROP `security_code`, DROP `security_code_created_at`, DROP `security_code_failures`, DROP `login_failures`, DROP `is_locked`');
    }
}
