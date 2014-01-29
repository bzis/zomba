<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140124191705 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE campaign_ban (user_id INT NOT NULL, campaign_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5EB434A4A76ED395 (user_id), INDEX IDX_5EB434A4F639F774 (campaign_id), PRIMARY KEY(user_id, campaign_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE campaign_ban ADD CONSTRAINT FK_5EB434A4A76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_ban ADD CONSTRAINT FK_5EB434A4F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE campaign_ban");
    }
}
