<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200313220738 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subjects ADD created_by_id CHAR(36) DEFAULT NULL AFTER `id`');
        $this->addSql('ALTER TABLE subjects ADD CONSTRAINT FK_AB259917B03A8386 FOREIGN KEY (created_by_id) REFERENCES `users` (id)');
        $this->addSql('CREATE INDEX IDX_AB259917B03A8386 ON subjects (created_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `subjects` DROP FOREIGN KEY FK_AB259917B03A8386');
        $this->addSql('DROP INDEX IDX_AB259917B03A8386 ON `subjects`');
        $this->addSql('ALTER TABLE `subjects` DROP created_by_id');
    }
}
