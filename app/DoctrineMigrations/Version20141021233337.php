<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141021233337 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("CREATE TABLE `daily_stats` (
  `campaign_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `views` int(11) NOT NULL,
  `paid_views` int(11) NOT NULL,
  `charged` decimal(7,2) NOT NULL,
  `paid` decimal(7,2) NOT NULL,
  KEY `platform_id` (`platform_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `date` (`date`),
  CONSTRAINT `daily_stats_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`),
  CONSTRAINT `daily_stats_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `platform` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `daily_stats`');
    }
}
