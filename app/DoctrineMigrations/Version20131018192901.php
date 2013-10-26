<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131018192901 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE campaign_platform");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE campaign_platform (campaign_id INT NOT NULL, platform_id INT NOT NULL, INDEX IDX_B2206B4F639F774 (campaign_id), INDEX IDX_B2206B4FFE6496F (platform_id), PRIMARY KEY(campaign_id, platform_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE campaign_platform ADD CONSTRAINT FK_B2206B4F639F774 FOREIGN KEY (campaign_id) REFERENCES Campaign (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_platform ADD CONSTRAINT FK_B2206B4FFE6496F FOREIGN KEY (platform_id) REFERENCES Platform (id) ON DELETE CASCADE");
    }
}
