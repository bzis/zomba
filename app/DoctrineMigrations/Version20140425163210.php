<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140425163210 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE credits CHANGE credited_amount credited_amount NUMERIC(9, 2) NOT NULL, CHANGE crediting_amount crediting_amount NUMERIC(9, 2) NOT NULL, CHANGE reversing_amount reversing_amount NUMERIC(9, 2) NOT NULL, CHANGE target_amount target_amount NUMERIC(9, 2) NOT NULL");
        $this->addSql("ALTER TABLE financial_transactions CHANGE processed_amount processed_amount NUMERIC(9, 2) NOT NULL, CHANGE requested_amount requested_amount NUMERIC(9, 2) NOT NULL");
        $this->addSql("ALTER TABLE payment_instructions CHANGE amount amount NUMERIC(9, 2) NOT NULL, CHANGE approved_amount approved_amount NUMERIC(9, 2) NOT NULL, CHANGE approving_amount approving_amount NUMERIC(9, 2) NOT NULL, CHANGE credited_amount credited_amount NUMERIC(9, 2) NOT NULL, CHANGE crediting_amount crediting_amount NUMERIC(9, 2) NOT NULL, CHANGE deposited_amount deposited_amount NUMERIC(9, 2) NOT NULL, CHANGE depositing_amount depositing_amount NUMERIC(9, 2) NOT NULL, CHANGE reversing_approved_amount reversing_approved_amount NUMERIC(9, 2) NOT NULL, CHANGE reversing_credited_amount reversing_credited_amount NUMERIC(9, 2) NOT NULL, CHANGE reversing_deposited_amount reversing_deposited_amount NUMERIC(9, 2) NOT NULL");
        $this->addSql("ALTER TABLE payments CHANGE approved_amount approved_amount NUMERIC(9, 2) NOT NULL, CHANGE approving_amount approving_amount NUMERIC(9, 2) NOT NULL, CHANGE credited_amount credited_amount NUMERIC(9, 2) NOT NULL, CHANGE crediting_amount crediting_amount NUMERIC(9, 2) NOT NULL, CHANGE deposited_amount deposited_amount NUMERIC(9, 2) NOT NULL, CHANGE depositing_amount depositing_amount NUMERIC(9, 2) NOT NULL, CHANGE reversing_approved_amount reversing_approved_amount NUMERIC(9, 2) NOT NULL, CHANGE reversing_credited_amount reversing_credited_amount NUMERIC(9, 2) NOT NULL, CHANGE reversing_deposited_amount reversing_deposited_amount NUMERIC(9, 2) NOT NULL, CHANGE target_amount target_amount NUMERIC(9, 2) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE credits CHANGE credited_amount credited_amount NUMERIC(10, 5) NOT NULL, CHANGE crediting_amount crediting_amount NUMERIC(10, 5) NOT NULL, CHANGE reversing_amount reversing_amount NUMERIC(10, 5) NOT NULL, CHANGE target_amount target_amount NUMERIC(10, 5) NOT NULL");
        $this->addSql("ALTER TABLE financial_transactions CHANGE processed_amount processed_amount NUMERIC(10, 5) NOT NULL, CHANGE requested_amount requested_amount NUMERIC(10, 5) NOT NULL");
        $this->addSql("ALTER TABLE payments CHANGE approved_amount approved_amount NUMERIC(10, 5) NOT NULL, CHANGE approving_amount approving_amount NUMERIC(10, 5) NOT NULL, CHANGE credited_amount credited_amount NUMERIC(10, 5) NOT NULL, CHANGE crediting_amount crediting_amount NUMERIC(10, 5) NOT NULL, CHANGE deposited_amount deposited_amount NUMERIC(10, 5) NOT NULL, CHANGE depositing_amount depositing_amount NUMERIC(10, 5) NOT NULL, CHANGE reversing_approved_amount reversing_approved_amount NUMERIC(10, 5) NOT NULL, CHANGE reversing_credited_amount reversing_credited_amount NUMERIC(10, 5) NOT NULL, CHANGE reversing_deposited_amount reversing_deposited_amount NUMERIC(10, 5) NOT NULL, CHANGE target_amount target_amount NUMERIC(10, 5) NOT NULL");
        $this->addSql("ALTER TABLE payment_instructions CHANGE amount amount NUMERIC(10, 5) NOT NULL, CHANGE approved_amount approved_amount NUMERIC(10, 5) NOT NULL, CHANGE approving_amount approving_amount NUMERIC(10, 5) NOT NULL, CHANGE credited_amount credited_amount NUMERIC(10, 5) NOT NULL, CHANGE crediting_amount crediting_amount NUMERIC(10, 5) NOT NULL, CHANGE deposited_amount deposited_amount NUMERIC(10, 5) NOT NULL, CHANGE depositing_amount depositing_amount NUMERIC(10, 5) NOT NULL, CHANGE reversing_approved_amount reversing_approved_amount NUMERIC(10, 5) NOT NULL, CHANGE reversing_credited_amount reversing_credited_amount NUMERIC(10, 5) NOT NULL, CHANGE reversing_deposited_amount reversing_deposited_amount NUMERIC(10, 5) NOT NULL");
    }
}
