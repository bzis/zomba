<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141013232105 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP INDEX is_paid_viewer ON video_views');
        $this->addSql('ALTER TABLE video_views ADD is_in_stats TINYINT(1) NOT NULL, CHANGE viewer_id viewer_id CHAR(43) NOT NULL DEFAULT \'\'');
        $this->addSql('CREATE INDEX is_paid_viewer ON video_views (viewer_id, campaign_id, is_paid, timestamp, is_in_stats)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP INDEX is_paid_viewer ON video_views');
        $this->addSql('ALTER TABLE video_views DROP is_in_stats, CHANGE viewer_id viewer_id CHAR(43) DEFAULT \'\' NOT NULL');
        $this->addSql('CREATE INDEX is_paid_viewer ON video_views (viewer_id, campaign_id, is_paid, timestamp)');
    }
}
