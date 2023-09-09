<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210305234624 extends AbstractMigration
{
    public function isTransactional() : bool
    {
        return false;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sessions CHANGE api_client_id `api_client_id` CHAR(40) NOT NULL, CHANGE old_api_token `old_api_token` CHAR(80) DEFAULT NULL, CHANGE current_api_token `current_api_token` CHAR(80) NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE token `token` CHAR(20) COLLATE \'ascii_general_ci\' DEFAULT NULL, CHANGE security_code `security_code` CHAR(9) COLLATE \'ascii_general_ci\' DEFAULT NULL, CHANGE security_code_failures `security_code_failures` SMALLINT NOT NULL, CHANGE authentication_failures `authentication_failures` SMALLINT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `sessions` CHANGE `api_client_id` api_client_id VARCHAR(255) CHARACTER SET ascii NOT NULL COLLATE `ascii_general_ci`, CHANGE `old_api_token` old_api_token VARCHAR(255) CHARACTER SET ascii DEFAULT NULL COLLATE `ascii_general_ci`, CHANGE `current_api_token` current_api_token VARCHAR(255) CHARACTER SET ascii NOT NULL COLLATE `ascii_general_ci`');
        $this->addSql('ALTER TABLE `users` CHANGE `authentication_failures` authentication_failures INT NOT NULL, CHANGE `token` token VARCHAR(255) CHARACTER SET ascii DEFAULT NULL COLLATE `ascii_general_ci`, CHANGE `security_code` security_code VARCHAR(255) CHARACTER SET ascii DEFAULT NULL COLLATE `ascii_general_ci`, CHANGE `security_code_failures` security_code_failures INT NOT NULL');
    }
}
