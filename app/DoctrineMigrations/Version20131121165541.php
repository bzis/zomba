<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131121165541 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE campaign ADD user_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_1F1512DDA76ED395 ON campaign (user_id)");
        $this->addSql("ALTER TABLE platform ADD user_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE platform ADD CONSTRAINT FK_3952D0CBA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_3952D0CBA76ED395 ON platform (user_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDA76ED395");
        $this->addSql("DROP INDEX IDX_1F1512DDA76ED395 ON campaign");
        $this->addSql("ALTER TABLE campaign DROP user_id");
        $this->addSql("ALTER TABLE platform DROP FOREIGN KEY FK_3952D0CBA76ED395");
        $this->addSql("DROP INDEX IDX_3952D0CBA76ED395 ON platform");
        $this->addSql("ALTER TABLE vifeed_user CHANGE type type VARCHAR(255) DEFAULT NULL");
    }
}
