<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210305190714 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE `courses` CHANGE `id` `id` char(36) COLLATE \'ascii_general_ci\' NOT NULL FIRST, CHANGE `subject_id` `subject_id` char(36) COLLATE \'ascii_general_ci\' NULL AFTER `id`, CHANGE `teacher_id` `teacher_id` char(36) COLLATE \'ascii_general_ci\' NULL AFTER `subject_id`, CHANGE `name` `name` varchar(255) COLLATE \'utf8mb4_unicode_ci\' NULL AFTER `teacher_id`, COLLATE \'utf8mb4_unicode_ci\'');
        $this->addSql('ALTER TABLE `course_subscriptions` CHANGE `course_id` `course_id` char(36) COLLATE \'ascii_general_ci\' NOT NULL FIRST, CHANGE `student_id` `student_id` char(36) COLLATE \'ascii_general_ci\' NOT NULL AFTER `course_id`, COLLATE \'ascii_general_ci\'');
        $this->addSql('ALTER TABLE `lessons` CHANGE `id` `id` char(36) COLLATE \'ascii_general_ci\' NOT NULL FIRST, CHANGE `course_id` `course_id` char(36) COLLATE \'ascii_general_ci\' NULL AFTER `id`, CHANGE `name` `name` varchar(255) COLLATE \'utf8mb4_unicode_ci\' NOT NULL AFTER `to`, COLLATE \'utf8mb4_unicode_ci\'');
        $this->addSql('ALTER TABLE `sessions` CHANGE `id` `id` char(36) COLLATE \'ascii_general_ci\' NOT NULL FIRST, CHANGE `user_id` `user_id` char(36) COLLATE \'ascii_general_ci\' NULL AFTER `id`, CHANGE `api_client_id` `api_client_id` varchar(255) COLLATE \'ascii_general_ci\' NOT NULL AFTER `user_id`, CHANGE `old_api_token` `old_api_token` varchar(255) COLLATE \'ascii_general_ci\' NULL AFTER `api_client_id`, CHANGE `current_api_token` `current_api_token` varchar(255) COLLATE \'ascii_general_ci\' NOT NULL AFTER `old_api_token`, COLLATE \'ascii_general_ci\'');
        $this->addSql('ALTER TABLE `subjects` CHANGE `id` `id` char(36) COLLATE \'ascii_general_ci\' NOT NULL FIRST, CHANGE `created_by_id` `created_by_id` char(36) COLLATE \'ascii_general_ci\' NULL AFTER `id`, CHANGE `name` `name` varchar(255) COLLATE \'utf8mb4_unicode_ci\' NOT NULL AFTER `created_by_id`, COLLATE \'utf8mb4_unicode_ci\'');
        $this->addSql('ALTER TABLE `users` CHANGE `id` `id` char(36) COLLATE \'ascii_general_ci\' NOT NULL FIRST, CHANGE `first_name` `first_name` varchar(255) COLLATE \'utf8mb4_unicode_ci\' NULL AFTER `id`, CHANGE `last_name` `last_name` varchar(255) COLLATE \'utf8mb4_unicode_ci\' NULL AFTER `first_name`, CHANGE `email` `email` varchar(255) COLLATE \'utf8mb4_unicode_ci\' NULL AFTER `last_name`, CHANGE `password_hash` `password_hash` varchar(255) COLLATE \'ascii_general_ci\' NULL AFTER `email`, CHANGE `password_algorithm` `password_algorithm` varchar(255) COLLATE \'ascii_general_ci\' NULL AFTER `password_hash`, CHANGE `timezone` `timezone` varchar(255) COLLATE \'ascii_general_ci\' NOT NULL AFTER `is_student`, CHANGE `token` `token` varchar(255) COLLATE \'ascii_general_ci\' NULL AFTER `timezone`, CHANGE `security_code` `security_code` varchar(255) COLLATE \'ascii_general_ci\' NULL AFTER `token_created_at`, COLLATE \'utf8mb4_unicode_ci\'');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE `courses` CHANGE `id` `id` char(36) COLLATE \'utf8_unicode_ci\' NOT NULL FIRST, CHANGE `subject_id` `subject_id` char(36) COLLATE \'utf8_unicode_ci\' NULL AFTER `id`, CHANGE `teacher_id` `teacher_id` char(36) COLLATE \'utf8_unicode_ci\' NULL AFTER `subject_id`, CHANGE `name` `name` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `teacher_id`, COLLATE \'utf8_unicode_ci\'');
        $this->addSql('ALTER TABLE `course_subscriptions` CHANGE `course_id` `course_id` char(36) COLLATE \'utf8_unicode_ci\' NOT NULL FIRST, CHANGE `student_id` `student_id` char(36) COLLATE \'utf8_unicode_ci\' NOT NULL AFTER `course_id`, COLLATE \'utf8_unicode_ci\'');
        $this->addSql('ALTER TABLE `lessons` CHANGE `id` `id` char(36) COLLATE \'utf8_unicode_ci\' NOT NULL FIRST, CHANGE `course_id` `course_id` char(36) COLLATE \'utf8_unicode_ci\' NULL AFTER `id`, CHANGE `name` `name` varchar(255) COLLATE \'utf8_unicode_ci\' NOT NULL AFTER `to`, COLLATE \'utf8_unicode_ci\'');
        $this->addSql('ALTER TABLE `sessions` CHANGE `id` `id` char(36) COLLATE \'utf8_unicode_ci\' NOT NULL FIRST, CHANGE `user_id` `user_id` char(36) COLLATE \'utf8_unicode_ci\' NULL AFTER `id`, CHANGE `api_client_id` `api_client_id` varchar(255) COLLATE \'utf8_unicode_ci\' NOT NULL AFTER `user_id`, CHANGE `old_api_token` `old_api_token` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `api_client_id`, CHANGE `current_api_token` `current_api_token` varchar(255) COLLATE \'utf8_unicode_ci\' NOT NULL AFTER `old_api_token`, COLLATE \'utf8_unicode_ci\'');
        $this->addSql('ALTER TABLE `subjects` CHANGE `id` `id` char(36) COLLATE \'utf8_unicode_ci\' NOT NULL FIRST, CHANGE `created_by_id` `created_by_id` char(36) COLLATE \'utf8_unicode_ci\' NULL AFTER `id`, CHANGE `name` `name` varchar(255) COLLATE \'utf8_unicode_ci\' NOT NULL AFTER `created_by_id`, COLLATE \'utf8_unicode_ci\'');
        $this->addSql('ALTER TABLE `users` CHANGE `id` `id` char(36) COLLATE \'utf8_unicode_ci\' NOT NULL FIRST, CHANGE `first_name` `first_name` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `id`, CHANGE `last_name` `last_name` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `first_name`, CHANGE `email` `email` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `last_name`, CHANGE `password_hash` `password_hash` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `email`, CHANGE `password_algorithm` `password_algorithm` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `password_hash`, CHANGE `timezone` `timezone` varchar(255) COLLATE \'utf8_unicode_ci\' NOT NULL AFTER `is_student`, CHANGE `token` `token` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `timezone`, CHANGE `security_code` `security_code` varchar(255) COLLATE \'utf8_unicode_ci\' NULL AFTER `token_created_at`, COLLATE \'utf8_unicode_ci\'');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}
