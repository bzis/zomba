<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140424181551 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE platform DROP FOREIGN KEY FK_3952D0CBC54C8C93");
        $this->addSql("DROP TABLE platform_type");
        $this->addSql("DROP INDEX IDX_3952D0CBC54C8C93 ON platform");
        $this->addSql("ALTER TABLE platform DROP type_id");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE platform_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE platform ADD type_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE platform ADD CONSTRAINT FK_3952D0CBC54C8C93 FOREIGN KEY (type_id) REFERENCES platform_type (id)");
        $this->addSql("CREATE INDEX IDX_3952D0CBC54C8C93 ON platform (type_id)");
    }
}
