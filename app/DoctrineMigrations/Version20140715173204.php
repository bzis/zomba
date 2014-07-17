<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140715173204 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, system ENUM('ОСН', 'УСН') NOT NULL, name VARCHAR(100) NOT NULL, contact_name VARCHAR(50) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, reason VARCHAR(50) DEFAULT NULL, details VARCHAR(500) DEFAULT NULL, address VARCHAR(500) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, UNIQUE INDEX UNIQ_4FBF094FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE company ADD CONSTRAINT FK_4FBF094FA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE company");
    }
}
