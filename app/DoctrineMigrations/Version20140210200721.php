<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140210200721 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE UNIQUE INDEX UNIQ_389B7835E237E06 ON tag (name)");
        $this->addSql("CREATE TABLE tagging (resource_type VARCHAR(50) NOT NULL, resource_id VARCHAR(50) NOT NULL, tag_id INT NOT NULL, INDEX IDX_A4AED123BAD26311 (tag_id), PRIMARY KEY(resource_type, resource_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE tagging ADD CONSTRAINT FK_A4AED123BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)");
        $this->addSql("DROP TABLE campaign_tag");
        $this->addSql("DROP TABLE platform_tag");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP INDEX UNIQ_389B7835E237E06 ON tag");
        $this->addSql("CREATE TABLE campaign_tag (campaign_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_3FC353C2F639F774 (campaign_id), INDEX IDX_3FC353C2BAD26311 (tag_id), PRIMARY KEY(campaign_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform_tag (platform_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_B5AF4590FFE6496F (platform_id), INDEX IDX_B5AF4590BAD26311 (tag_id), PRIMARY KEY(platform_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE campaign_tag ADD CONSTRAINT FK_3FC353C2BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_tag ADD CONSTRAINT FK_3FC353C2F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_tag ADD CONSTRAINT FK_B5AF4590BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_tag ADD CONSTRAINT FK_B5AF4590FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE");
        $this->addSql("DROP TABLE tagging");
    }
}
