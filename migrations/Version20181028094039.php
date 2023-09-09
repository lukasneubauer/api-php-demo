<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181028094039 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE course_student DROP FOREIGN KEY FK_BFE0AADF59312FD6');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB923EDC87');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB941807E1D');
        $this->addSql('ALTER TABLE course_student DROP FOREIGN KEY FK_BFE0AADF5A19F86B');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('CREATE TABLE `users` (`id` CHAR(36) NOT NULL, `first_name` VARCHAR(255) DEFAULT NULL, `last_name` VARCHAR(255) DEFAULT NULL, `email` VARCHAR(255) DEFAULT NULL, `password` VARCHAR(255) DEFAULT NULL, `is_teacher` TINYINT(1) NOT NULL, `is_student` TINYINT(1) NOT NULL, `price` INT DEFAULT NULL, `token` VARCHAR(255) DEFAULT NULL, `token_created_at` DATETIME DEFAULT NULL, `security_code` VARCHAR(255) DEFAULT NULL, `security_code_created_at` DATETIME DEFAULT NULL, `security_code_failures` INT NOT NULL, `login_failures` INT NOT NULL, `is_locked` TINYINT(1) NOT NULL, `is_active` TINYINT(1) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E96F279BB4 (`email`), UNIQUE INDEX UNIQ_1483A5E989FC6268 (`token`), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `courses` (`id` CHAR(36) NOT NULL, subject_id CHAR(36) DEFAULT NULL, teacher_id CHAR(36) DEFAULT NULL, `name` VARCHAR(255) DEFAULT NULL, `is_active` TINYINT(1) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, INDEX IDX_A9A55A4C23EDC87 (subject_id), INDEX IDX_A9A55A4C41807E1D (teacher_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `course_subscriptions` (`course_id` CHAR(36) NOT NULL, `student_id` CHAR(36) NOT NULL, INDEX IDX_C7FACED859312FD6 (`course_id`), INDEX IDX_C7FACED85A19F86B (`student_id`), PRIMARY KEY(`course_id`, `student_id`)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `subjects` (`id` CHAR(36) NOT NULL, `name` VARCHAR(255) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, UNIQUE INDEX UNIQ_AB259917999517A (`name`), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `lessons` (`id` CHAR(36) NOT NULL, course_id CHAR(36) DEFAULT NULL, `from` DATETIME NOT NULL, `to` DATETIME NOT NULL, `name` VARCHAR(255) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, INDEX IDX_3F4218D9591CC992 (course_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `sessions` (`id` CHAR(36) NOT NULL, user_id CHAR(36) DEFAULT NULL, `application_type` INT NOT NULL, `api_client_id` VARCHAR(255) NOT NULL, `api_token` VARCHAR(255) NOT NULL, `is_locked` TINYINT(1) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, UNIQUE INDEX UNIQ_9A609D1370CA5872 (`api_token`), INDEX IDX_9A609D13A76ED395 (user_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `courses` ADD CONSTRAINT FK_A9A55A4C23EDC87 FOREIGN KEY (subject_id) REFERENCES `subjects` (id)');
        $this->addSql('ALTER TABLE `courses` ADD CONSTRAINT FK_A9A55A4C41807E1D FOREIGN KEY (teacher_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE `course_subscriptions` ADD CONSTRAINT FK_C7FACED859312FD6 FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)');
        $this->addSql('ALTER TABLE `course_subscriptions` ADD CONSTRAINT FK_C7FACED85A19F86B FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)');
        $this->addSql('ALTER TABLE `lessons` ADD CONSTRAINT FK_3F4218D9591CC992 FOREIGN KEY (course_id) REFERENCES `courses` (id)');
        $this->addSql('ALTER TABLE `sessions` ADD CONSTRAINT FK_9A609D13A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE course_student');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE subject');
        $this->addSql('DROP TABLE user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `courses` DROP FOREIGN KEY FK_A9A55A4C41807E1D');
        $this->addSql('ALTER TABLE `course_subscriptions` DROP FOREIGN KEY FK_C7FACED85A19F86B');
        $this->addSql('ALTER TABLE `sessions` DROP FOREIGN KEY FK_9A609D13A76ED395');
        $this->addSql('ALTER TABLE `course_subscriptions` DROP FOREIGN KEY FK_C7FACED859312FD6');
        $this->addSql('ALTER TABLE `lessons` DROP FOREIGN KEY FK_3F4218D9591CC992');
        $this->addSql('ALTER TABLE `courses` DROP FOREIGN KEY FK_A9A55A4C23EDC87');
        $this->addSql('CREATE TABLE course (id CHAR(36) NOT NULL COLLATE utf8_unicode_ci, subject_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci, teacher_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci, name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_169E6FB923EDC87 (subject_id), INDEX IDX_169E6FB941807E1D (teacher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_student (course_id CHAR(36) NOT NULL COLLATE utf8_unicode_ci, student_id CHAR(36) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_BFE0AADF59312FD6 (course_id), INDEX IDX_BFE0AADF5A19F86B (student_id), PRIMARY KEY(course_id, student_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson (id CHAR(36) NOT NULL COLLATE utf8_unicode_ci, course_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci, `from` DATETIME NOT NULL, `to` DATETIME NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F87474F3591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id CHAR(36) NOT NULL COLLATE utf8_unicode_ci, user_id CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci, application_type INT NOT NULL, api_client_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, api_token VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, is_locked TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D044D5D470CA5872 (api_token), INDEX IDX_D044D5D4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subject (id CHAR(36) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_FBCE3E7A999517A (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COLLATE utf8_unicode_ci, first_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, last_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, email VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, password VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, is_teacher TINYINT(1) NOT NULL, is_student TINYINT(1) NOT NULL, price INT DEFAULT NULL, token VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, token_created_at DATETIME DEFAULT NULL, security_code VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, security_code_created_at DATETIME DEFAULT NULL, security_code_failures INT NOT NULL, login_failures INT NOT NULL, is_locked TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D6496F279BB4 (email), UNIQUE INDEX UNIQ_8D93D64989FC6268 (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB923EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB941807E1D FOREIGN KEY (teacher_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course_student ADD CONSTRAINT FK_BFE0AADF59312FD6 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course_student ADD CONSTRAINT FK_BFE0AADF5A19F86B FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE `users`');
        $this->addSql('DROP TABLE `courses`');
        $this->addSql('DROP TABLE `course_subscriptions`');
        $this->addSql('DROP TABLE `subjects`');
        $this->addSql('DROP TABLE `lessons`');
        $this->addSql('DROP TABLE `sessions`');
    }
}
