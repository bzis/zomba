<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140206152208 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E9295258BAC62AF");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525F639F774");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525F92F3E70");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525FFE6496F");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E9295258BAC62AF FOREIGN KEY (city_id) REFERENCES net_city (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525F92F3E70 FOREIGN KEY (country_id) REFERENCES net_country (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525FFE6496F");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525F639F774");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525F92F3E70");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E9295258BAC62AF");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525F92F3E70 FOREIGN KEY (country_id) REFERENCES net_country (id)");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E9295258BAC62AF FOREIGN KEY (city_id) REFERENCES net_city (id)");
    }
}
