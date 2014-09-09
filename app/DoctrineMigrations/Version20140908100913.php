<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140908100913 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE company ADD position VARCHAR(50) NOT NULL, ADD inn VARCHAR(12) NOT NULL, ADD kpp VARCHAR(9) NOT NULL, ADD bic VARCHAR(9) NOT NULL, ADD bank_account VARCHAR(20) NOT NULL, ADD correspondent_account VARCHAR(20) NOT NULL, ADD is_approved TINYINT(1) NOT NULL, DROP role, DROP reason, DROP details, CHANGE contact_name contact_name VARCHAR(50) NOT NULL, CHANGE address address VARCHAR(500) NOT NULL, CHANGE phone phone VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE company ADD role VARCHAR(50) DEFAULT NULL, ADD reason VARCHAR(50) DEFAULT NULL, ADD details VARCHAR(500) DEFAULT NULL, DROP position, DROP inn, DROP kpp, DROP bic, DROP bank_account, DROP correspondent_account, DROP is_approved, CHANGE contact_name contact_name VARCHAR(50) DEFAULT NULL, CHANGE address address VARCHAR(500) DEFAULT NULL, CHANGE phone phone VARCHAR(30) DEFAULT NULL');
    }
}
