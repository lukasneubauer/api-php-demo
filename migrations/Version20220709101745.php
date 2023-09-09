<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220709101745 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscriptions DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE subscriptions DROP id, DROP is_paid, DROP created_at, DROP updated_at, CHANGE course_id `course_id` CHAR(36) NOT NULL, CHANGE student_id `student_id` CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE subscriptions ADD PRIMARY KEY (`course_id`, `student_id`)');
        $this->addSql('ALTER TABLE subscriptions RENAME INDEX idx_4778a01591cc992 TO IDX_4778A0159312FD6');
        $this->addSql('ALTER TABLE subscriptions RENAME INDEX idx_4778a01cb944f1a TO IDX_4778A015A19F86B');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `subscriptions` DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE `subscriptions` ADD id CHAR(36) NOT NULL FIRST, ADD is_paid TINYINT(1) NOT NULL AFTER student_id, ADD created_at DATETIME NOT NULL AFTER is_paid, ADD updated_at DATETIME NOT NULL AFTER created_at, CHANGE `course_id` course_id CHAR(36) DEFAULT NULL, CHANGE `student_id` student_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE `subscriptions` ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE `subscriptions` RENAME INDEX idx_4778a0159312fd6 TO IDX_4778A01591CC992');
        $this->addSql('ALTER TABLE `subscriptions` RENAME INDEX idx_4778a015a19f86b TO IDX_4778A01CB944F1A');
    }
}
