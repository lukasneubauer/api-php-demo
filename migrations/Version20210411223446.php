<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210411223446 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE courses CHANGE price `price` INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE security_code_failures `security_code_failures` SMALLINT UNSIGNED NOT NULL, CHANGE authentication_failures `authentication_failures` SMALLINT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `courses` CHANGE `price` price INT NOT NULL');
        $this->addSql('ALTER TABLE `users` CHANGE `authentication_failures` authentication_failures SMALLINT NOT NULL, CHANGE `security_code_failures` security_code_failures SMALLINT NOT NULL');
    }
}
