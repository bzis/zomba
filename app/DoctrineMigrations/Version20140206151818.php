<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140206151818 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE platform_video");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE platform_video (id INT AUTO_INCREMENT NOT NULL, campaign_id INT DEFAULT NULL, platform_id INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, vk_id INT DEFAULT NULL, INDEX IDX_4E9BBEA8FFE6496F (platform_id), INDEX IDX_4E9BBEA8F639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE platform_video ADD CONSTRAINT FK_4E9BBEA8F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)");
        $this->addSql("ALTER TABLE platform_video ADD CONSTRAINT FK_4E9BBEA8FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)");
    }
}
