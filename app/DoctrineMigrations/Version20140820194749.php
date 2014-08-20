<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140820194749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE net_city_ip (city_id INT DEFAULT NULL, begin_ip BIGINT DEFAULT NULL, end_ip BIGINT DEFAULT NULL, INDEX city_id (city_id), INDEX ip (begin_ip)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE net_country_ip (country_id INT DEFAULT 0, begin_ip BIGINT DEFAULT NULL, end_ip BIGINT DEFAULT '0', INDEX country_id (country_id), INDEX ip (begin_ip)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE net_euro (country_id INT DEFAULT 0, begin_ip BIGINT DEFAULT NULL, end_ip BIGINT DEFAULT '0', INDEX country_id (country_id), INDEX ip (begin_ip)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE net_ru (city_id INT DEFAULT 0, begin_ip BIGINT DEFAULT NULL, end_ip BIGINT DEFAULT NULL, INDEX city_id (city_id), INDEX ip (begin_ip)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DROP TABLE net_city_ip");
        $this->addSql("DROP TABLE net_country_ip");
        $this->addSql("DROP TABLE net_euro");
        $this->addSql("DROP TABLE net_ru");
    }
}
