<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140820195455 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE campaign DROP duration");
        $this->addSql("DROP INDEX is_geo_detected ON video_views");
        $this->addSql("DROP INDEX is_paid ON video_views");
        $this->addSql("DROP INDEX timestamp ON video_views");
        $this->addSql("DROP INDEX viewer_id ON video_views");
        $this->addSql("ALTER TABLE video_views DROP is_geo_detected, DROP watching_now_processed");
        $this->addSql("CREATE INDEX is_paid_viewer ON video_views (viewer_id, campaign_id, is_paid, timestamp)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE campaign ADD duration INT DEFAULT NULL");
        $this->addSql("DROP INDEX is_paid_viewer ON video_views");
        $this->addSql("ALTER TABLE video_views ADD is_geo_detected TINYINT(1) NOT NULL, ADD watching_now_processed TINYINT(1) NOT NULL");
        $this->addSql("CREATE INDEX is_geo_detected ON video_views (is_geo_detected)");
        $this->addSql("CREATE INDEX is_paid ON video_views (is_paid)");
        $this->addSql("CREATE INDEX timestamp ON video_views (timestamp)");
        $this->addSql("CREATE INDEX viewer_id ON video_views (viewer_id)");
    }
}
