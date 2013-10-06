<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131003154135 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabaseplatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE platform_country (platform_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_46430334FFE6496F (platform_id), INDEX IDX_46430334F92F3E70 (country_id), PRIMARY KEY(platform_id, country_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform_tag (platform_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_B5AF4590FFE6496F (platform_id), INDEX IDX_B5AF4590BAD26311 (tag_id), PRIMARY KEY(platform_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE platform_country ADD CONSTRAINT FK_46430334FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_country ADD CONSTRAINT FK_46430334F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_tag ADD CONSTRAINT FK_B5AF4590FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_tag ADD CONSTRAINT FK_B5AF4590BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform ADD type_id INT DEFAULT NULL, ADD url VARCHAR(255) NOT NULL, ADD description VARCHAR(255) NOT NULL");
        $this->addSql("ALTER TABLE platform ADD CONSTRAINT FK_3952D0CBC54C8C93 FOREIGN KEY (type_id) REFERENCES platform_type (id)");
        $this->addSql("CREATE INDEX IDX_3952D0CBC54C8C93 ON platform (type_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabaseplatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE platform DROP FOREIGN KEY FK_3952D0CBC54C8C93");
        $this->addSql("DROP TABLE platform_country");
        $this->addSql("DROP TABLE platform_tag");
        $this->addSql("DROP TABLE platform_type");
        $this->addSql("DROP INDEX IDX_3952D0CBC54C8C93 ON platform");
        $this->addSql("ALTER TABLE platform DROP type_id, DROP url, DROP description");
    }
}
