<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220626013902 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `subscriptions` (`id` CHAR(36) NOT NULL, course_id CHAR(36) DEFAULT NULL, student_id CHAR(36) DEFAULT NULL, `is_payed` TINYINT(1) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, INDEX IDX_4778A01591CC992 (course_id), INDEX IDX_4778A01CB944F1A (student_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `subscriptions` ADD CONSTRAINT FK_4778A01591CC992 FOREIGN KEY (course_id) REFERENCES `courses` (id)');
        $this->addSql('ALTER TABLE `subscriptions` ADD CONSTRAINT FK_4778A01CB944F1A FOREIGN KEY (student_id) REFERENCES `users` (id)');
        $this->addSql('DROP TABLE course_subscriptions');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE course_subscriptions (course_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, student_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C7FACED859312FD6 (course_id), INDEX IDX_C7FACED85A19F86B (student_id), PRIMARY KEY(course_id, student_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE course_subscriptions ADD CONSTRAINT FK_C7FACED859312FD6 FOREIGN KEY (course_id) REFERENCES courses (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE course_subscriptions ADD CONSTRAINT FK_C7FACED85A19F86B FOREIGN KEY (student_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE `subscriptions`');
    }
}
