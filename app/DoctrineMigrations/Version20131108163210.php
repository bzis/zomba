<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131108163210 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52AFD913E4D");
        $this->addSql("DROP INDEX UNIQ_A260A52AFD913E4D ON payment_order");
        $this->addSql("ALTER TABLE payment_order CHANGE paymentinstruction_id payment_instruction_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52A8789B572 FOREIGN KEY (payment_instruction_id) REFERENCES payment_instructions (id)");
        $this->addSql("ALTER TABLE payment_order CHANGE amount amount NUMERIC(9, 2) NOT NULL");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_A260A52A8789B572 ON payment_order (payment_instruction_id)");
        $this->addSql("ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52AA76ED395");
        $this->addSql("ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52AA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");


    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52A8789B572");
        $this->addSql("DROP INDEX UNIQ_A260A52A8789B572 ON payment_order");
        $this->addSql("ALTER TABLE payment_order CHANGE payment_instruction_id paymentInstruction_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52AFD913E4D FOREIGN KEY (paymentInstruction_id) REFERENCES payment_instructions (id)");
        $this->addSql("ALTER TABLE payment_order CHANGE amount amount NUMERIC(2, 0) NOT NULL");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_A260A52AFD913E4D ON payment_order (paymentInstruction_id)");
        $this->addSql("ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52AA76ED395");
        $this->addSql("ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52AA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id)");

    }
}
