<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141001132702 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE campaign CHANGE hash_id hash_id varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL');
        $this->addSql('ALTER TABLE platform CHANGE hash_id hash_id varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE campaign CHANGE hash_id hash_id varchar(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE platform CHANGE hash_id hash_id varchar(10) DEFAULT NULL');
    }
}
