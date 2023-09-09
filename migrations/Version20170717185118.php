<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20170717185118 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `user` (`id` CHAR(36) NOT NULL, `full_name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL, `password` VARCHAR(255) NOT NULL, `is_teacher` TINYINT(1) NOT NULL, `is_student` TINYINT(1) NOT NULL, `price` NUMERIC(10, 2) DEFAULT NULL, `token` CHAR(32) DEFAULT NULL, `api_token` CHAR(32) NOT NULL, `is_active` TINYINT(1) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D6496F279BB4 (`email`), UNIQUE INDEX UNIQ_8D93D64989FC6268 (`token`), UNIQUE INDEX UNIQ_8D93D64970CA5872 (`api_token`), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `lesson` (`id` CHAR(36) NOT NULL, course_id CHAR(36) DEFAULT NULL, `from` DATETIME NOT NULL, `to` DATETIME NOT NULL, `name` VARCHAR(255) DEFAULT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, INDEX IDX_F87474F3591CC992 (course_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `subject` (`id` CHAR(36) NOT NULL, `name` VARCHAR(255) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, UNIQUE INDEX UNIQ_FBCE3E7A999517A (`name`), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `course` (`id` CHAR(36) NOT NULL, subject_id CHAR(36) DEFAULT NULL, teacher_id CHAR(36) DEFAULT NULL, `name` VARCHAR(255) DEFAULT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, INDEX IDX_169E6FB923EDC87 (subject_id), INDEX IDX_169E6FB941807E1D (teacher_id), PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `course_student` (`course_id` CHAR(36) NOT NULL, `student_id` CHAR(36) NOT NULL, INDEX IDX_BFE0AADF59312FD6 (`course_id`), INDEX IDX_BFE0AADF5A19F86B (`student_id`), PRIMARY KEY(`course_id`, `student_id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `lesson` ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES `course` (id)');
        $this->addSql('ALTER TABLE `course` ADD CONSTRAINT FK_169E6FB923EDC87 FOREIGN KEY (subject_id) REFERENCES `subject` (id)');
        $this->addSql('ALTER TABLE `course` ADD CONSTRAINT FK_169E6FB941807E1D FOREIGN KEY (teacher_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `course_student` ADD CONSTRAINT FK_BFE0AADF59312FD6 FOREIGN KEY (`course_id`) REFERENCES `course` (`id`)');
        $this->addSql('ALTER TABLE `course_student` ADD CONSTRAINT FK_BFE0AADF5A19F86B FOREIGN KEY (`student_id`) REFERENCES `user` (`id`)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `course` DROP FOREIGN KEY FK_169E6FB941807E1D');
        $this->addSql('ALTER TABLE `course_student` DROP FOREIGN KEY FK_BFE0AADF5A19F86B');
        $this->addSql('ALTER TABLE `course` DROP FOREIGN KEY FK_169E6FB923EDC87');
        $this->addSql('ALTER TABLE `lesson` DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE `course_student` DROP FOREIGN KEY FK_BFE0AADF59312FD6');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE `lesson`');
        $this->addSql('DROP TABLE `subject`');
        $this->addSql('DROP TABLE `course`');
        $this->addSql('DROP TABLE `course_student`');
    }
}
