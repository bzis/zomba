<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140201153512 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE platform DROP FOREIGN KEY `FK_3952D0CBA76ED395`");
        $this->addSql("ALTER TABLE platform CHANGE user_id user_id INT NOT NULL");
        $this->addSql("ALTER TABLE platform ADD CONSTRAINT FK_3952D0CBA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_3952D0CBF47645AE ON platform (url)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP INDEX UNIQ_3952D0CBF47645AE ON platform");
        $this->addSql("ALTER TABLE platform CHANGE user_id user_id INT DEFAULT NULL");
    }
}
