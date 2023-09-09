<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220717164725 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `payments` (`id` CHAR(36) NOT NULL, course_id CHAR(36) DEFAULT NULL, student_id CHAR(36) DEFAULT NULL, `price` INT UNSIGNED NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, INDEX IDX_65D29B32591CC992 (course_id), INDEX IDX_65D29B32CB944F1A (student_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `payments` ADD CONSTRAINT FK_65D29B32591CC992 FOREIGN KEY (course_id) REFERENCES `courses` (id)');
        $this->addSql('ALTER TABLE `payments` ADD CONSTRAINT FK_65D29B32CB944F1A FOREIGN KEY (student_id) REFERENCES `users` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `payments`');
    }
}
