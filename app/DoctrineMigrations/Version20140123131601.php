<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140123131601 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE credits (id INT AUTO_INCREMENT NOT NULL, payment_instruction_id INT NOT NULL, payment_id INT DEFAULT NULL, attention_required TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, credited_amount NUMERIC(10, 5) NOT NULL, crediting_amount NUMERIC(10, 5) NOT NULL, reversing_amount NUMERIC(10, 5) NOT NULL, state SMALLINT NOT NULL, target_amount NUMERIC(10, 5) NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_4117D17E8789B572 (payment_instruction_id), INDEX IDX_4117D17E4C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE financial_transactions (id INT AUTO_INCREMENT NOT NULL, credit_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, extended_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:extended_payment_data)', processed_amount NUMERIC(10, 5) NOT NULL, reason_code VARCHAR(100) DEFAULT NULL, reference_number VARCHAR(100) DEFAULT NULL, requested_amount NUMERIC(10, 5) NOT NULL, response_code VARCHAR(100) DEFAULT NULL, state SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, tracking_id VARCHAR(100) DEFAULT NULL, transaction_type SMALLINT NOT NULL, INDEX IDX_1353F2D9CE062FF9 (credit_id), INDEX IDX_1353F2D94C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE payments (id INT AUTO_INCREMENT NOT NULL, payment_instruction_id INT NOT NULL, approved_amount NUMERIC(10, 5) NOT NULL, approving_amount NUMERIC(10, 5) NOT NULL, credited_amount NUMERIC(10, 5) NOT NULL, crediting_amount NUMERIC(10, 5) NOT NULL, deposited_amount NUMERIC(10, 5) NOT NULL, depositing_amount NUMERIC(10, 5) NOT NULL, expiration_date DATETIME DEFAULT NULL, reversing_approved_amount NUMERIC(10, 5) NOT NULL, reversing_credited_amount NUMERIC(10, 5) NOT NULL, reversing_deposited_amount NUMERIC(10, 5) NOT NULL, state SMALLINT NOT NULL, target_amount NUMERIC(10, 5) NOT NULL, attention_required TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_65D29B328789B572 (payment_instruction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE payment_instructions (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(10, 5) NOT NULL, approved_amount NUMERIC(10, 5) NOT NULL, approving_amount NUMERIC(10, 5) NOT NULL, created_at DATETIME NOT NULL, credited_amount NUMERIC(10, 5) NOT NULL, crediting_amount NUMERIC(10, 5) NOT NULL, currency VARCHAR(3) NOT NULL, deposited_amount NUMERIC(10, 5) NOT NULL, depositing_amount NUMERIC(10, 5) NOT NULL, extended_data LONGTEXT NOT NULL COMMENT '(DC2Type:extended_payment_data)', payment_system_name VARCHAR(100) NOT NULL, reversing_approved_amount NUMERIC(10, 5) NOT NULL, reversing_credited_amount NUMERIC(10, 5) NOT NULL, reversing_deposited_amount NUMERIC(10, 5) NOT NULL, state SMALLINT NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE age_range (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE campaign (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, gender ENUM('male', 'female'), max_bid NUMERIC(5, 2) NOT NULL, budget NUMERIC(8, 2) NOT NULL, daily_budget NUMERIC(8, 2) NOT NULL, budget_used NUMERIC(8, 2) NOT NULL, daily_budget_used NUMERIC(8, 2) NOT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, total_views INT DEFAULT NULL, bid NUMERIC(5, 2) NOT NULL, duration INT DEFAULT NULL, status ENUM('on', 'paused', 'ended', 'awaiting', 'archived') NOT NULL, INDEX IDX_1F1512DDA76ED395 (user_id), INDEX status (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE campaign_country (campaign_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_FBC9554AF639F774 (campaign_id), INDEX IDX_FBC9554AF92F3E70 (country_id), PRIMARY KEY(campaign_id, country_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE campaign_tag (campaign_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_3FC353C2F639F774 (campaign_id), INDEX IDX_3FC353C2BAD26311 (tag_id), PRIMARY KEY(campaign_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE campaign_age_range (campaign_id INT NOT NULL, agerange_id INT NOT NULL, INDEX IDX_C64440A4F639F774 (campaign_id), INDEX IDX_C64440A490B22C2E (agerange_id), PRIMARY KEY(campaign_id, agerange_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE vifeed_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', UNIQUE INDEX UNIQ_E0FE35C95E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE vifeed_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, type ENUM('advertiser', 'publisher'), vk_id VARCHAR(255) DEFAULT NULL, social_data TEXT DEFAULT NULL COMMENT '(DC2Type:array)', balance NUMERIC(9, 2) NOT NULL, UNIQUE INDEX UNIQ_BBEF2F5692FC23A8 (username_canonical), UNIQUE INDEX UNIQ_BBEF2F56A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE vifeed_user_groups (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_DF562C08A76ED395 (user_id), INDEX IDX_DF562C08FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, discr VARCHAR(255) NOT NULL, vk_id INT DEFAULT NULL, INDEX IDX_3952D0CBA76ED395 (user_id), INDEX IDX_3952D0CBC54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform_country (platform_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_46430334FFE6496F (platform_id), INDEX IDX_46430334F92F3E70 (country_id), PRIMARY KEY(platform_id, country_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform_tag (platform_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_B5AF4590FFE6496F (platform_id), INDEX IDX_B5AF4590BAD26311 (tag_id), PRIMARY KEY(platform_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE platform_video (id INT AUTO_INCREMENT NOT NULL, platform_id INT DEFAULT NULL, campaign_id INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, vk_id INT DEFAULT NULL, INDEX IDX_4E9BBEA8FFE6496F (platform_id), INDEX IDX_4E9BBEA8F639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE video_views (id INT AUTO_INCREMENT NOT NULL, platform_id INT DEFAULT NULL, campaign_id INT DEFAULT NULL, country_id INT DEFAULT NULL, city_id INT DEFAULT NULL, `current_time` INT NOT NULL, timestamp INT NOT NULL, track_number INT NOT NULL, ip INT DEFAULT NULL, is_geo_detected TINYINT(1) NOT NULL, is_paid TINYINT(1) NOT NULL, INDEX IDX_9E929525FFE6496F (platform_id), INDEX IDX_9E929525F639F774 (campaign_id), INDEX IDX_9E929525F92F3E70 (country_id), INDEX IDX_9E9295258BAC62AF (city_id), INDEX is_geo_detected (is_geo_detected), INDEX is_paid (is_paid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE payment_order (id INT AUTO_INCREMENT NOT NULL, payment_instruction_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL, amount NUMERIC(9, 2) NOT NULL, UNIQUE INDEX UNIQ_A260A52A8789B572 (payment_instruction_id), INDEX IDX_A260A52AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE video_view_payment (id INT AUTO_INCREMENT NOT NULL, video_view_id INT DEFAULT NULL, charged NUMERIC(5, 2) NOT NULL, comission NUMERIC(5, 2) NOT NULL, paid NUMERIC(5, 2) NOT NULL, UNIQUE INDEX UNIQ_E5868038E6F8F16A (video_view_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE wallet (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(10) NOT NULL, number VARCHAR(20) NOT NULL, INDEX IDX_7C68921FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE withdrawal (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, wallet_id INT NOT NULL, amount NUMERIC(9, 2) NOT NULL, status INT NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_6D2D3B45A76ED395 (user_id), INDEX IDX_6D2D3B45712520F3 (wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE net_city (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, name_ru VARCHAR(100) DEFAULT NULL, name_en VARCHAR(100) DEFAULT NULL, region VARCHAR(2) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, latitude VARCHAR(10) DEFAULT NULL, longitude VARCHAR(10) DEFAULT NULL, INDEX IDX_5F22C447F92F3E70 (country_id), INDEX name_ru (name_ru), INDEX name_en (name_en), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE net_country (id INT AUTO_INCREMENT NOT NULL, name_ru VARCHAR(100) DEFAULT NULL, name_en VARCHAR(100) DEFAULT NULL, code VARCHAR(2) DEFAULT NULL, INDEX code (code), INDEX name_ru (name_ru), INDEX name_en (name_en), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE credits ADD CONSTRAINT FK_4117D17E8789B572 FOREIGN KEY (payment_instruction_id) REFERENCES payment_instructions (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE credits ADD CONSTRAINT FK_4117D17E4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE financial_transactions ADD CONSTRAINT FK_1353F2D9CE062FF9 FOREIGN KEY (credit_id) REFERENCES credits (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE financial_transactions ADD CONSTRAINT FK_1353F2D94C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE payments ADD CONSTRAINT FK_65D29B328789B572 FOREIGN KEY (payment_instruction_id) REFERENCES payment_instructions (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_country ADD CONSTRAINT FK_FBC9554AF639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_country ADD CONSTRAINT FK_FBC9554AF92F3E70 FOREIGN KEY (country_id) REFERENCES net_country (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_tag ADD CONSTRAINT FK_3FC353C2F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_tag ADD CONSTRAINT FK_3FC353C2BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_age_range ADD CONSTRAINT FK_C64440A4F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE campaign_age_range ADD CONSTRAINT FK_C64440A490B22C2E FOREIGN KEY (agerange_id) REFERENCES age_range (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE vifeed_user_groups ADD CONSTRAINT FK_DF562C08A76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id)");
        $this->addSql("ALTER TABLE vifeed_user_groups ADD CONSTRAINT FK_DF562C08FE54D947 FOREIGN KEY (group_id) REFERENCES vifeed_group (id)");
        $this->addSql("ALTER TABLE platform ADD CONSTRAINT FK_3952D0CBA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform ADD CONSTRAINT FK_3952D0CBC54C8C93 FOREIGN KEY (type_id) REFERENCES platform_type (id)");
        $this->addSql("ALTER TABLE platform_country ADD CONSTRAINT FK_46430334FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_country ADD CONSTRAINT FK_46430334F92F3E70 FOREIGN KEY (country_id) REFERENCES net_country (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_tag ADD CONSTRAINT FK_B5AF4590FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_tag ADD CONSTRAINT FK_B5AF4590BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE platform_video ADD CONSTRAINT FK_4E9BBEA8FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)");
        $this->addSql("ALTER TABLE platform_video ADD CONSTRAINT FK_4E9BBEA8F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E929525F92F3E70 FOREIGN KEY (country_id) REFERENCES net_country (id)");
        $this->addSql("ALTER TABLE video_views ADD CONSTRAINT FK_9E9295258BAC62AF FOREIGN KEY (city_id) REFERENCES net_city (id)");
        $this->addSql("ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52A8789B572 FOREIGN KEY (payment_instruction_id) REFERENCES payment_instructions (id)");
        $this->addSql("ALTER TABLE payment_order ADD CONSTRAINT FK_A260A52AA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE video_view_payment ADD CONSTRAINT FK_E5868038E6F8F16A FOREIGN KEY (video_view_id) REFERENCES video_views (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FA76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE withdrawal ADD CONSTRAINT FK_6D2D3B45A76ED395 FOREIGN KEY (user_id) REFERENCES vifeed_user (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE withdrawal ADD CONSTRAINT FK_6D2D3B45712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE net_city ADD CONSTRAINT FK_5F22C447F92F3E70 FOREIGN KEY (country_id) REFERENCES net_country (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE financial_transactions DROP FOREIGN KEY FK_1353F2D9CE062FF9");
        $this->addSql("ALTER TABLE credits DROP FOREIGN KEY FK_4117D17E4C3A3BB");
        $this->addSql("ALTER TABLE financial_transactions DROP FOREIGN KEY FK_1353F2D94C3A3BB");
        $this->addSql("ALTER TABLE credits DROP FOREIGN KEY FK_4117D17E8789B572");
        $this->addSql("ALTER TABLE payments DROP FOREIGN KEY FK_65D29B328789B572");
        $this->addSql("ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52A8789B572");
        $this->addSql("ALTER TABLE campaign_age_range DROP FOREIGN KEY FK_C64440A490B22C2E");
        $this->addSql("ALTER TABLE campaign_country DROP FOREIGN KEY FK_FBC9554AF639F774");
        $this->addSql("ALTER TABLE campaign_tag DROP FOREIGN KEY FK_3FC353C2F639F774");
        $this->addSql("ALTER TABLE campaign_age_range DROP FOREIGN KEY FK_C64440A4F639F774");
        $this->addSql("ALTER TABLE platform_video DROP FOREIGN KEY FK_4E9BBEA8F639F774");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525F639F774");
        $this->addSql("ALTER TABLE campaign_tag DROP FOREIGN KEY FK_3FC353C2BAD26311");
        $this->addSql("ALTER TABLE platform_tag DROP FOREIGN KEY FK_B5AF4590BAD26311");
        $this->addSql("ALTER TABLE vifeed_user_groups DROP FOREIGN KEY FK_DF562C08FE54D947");
        $this->addSql("ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDA76ED395");
        $this->addSql("ALTER TABLE vifeed_user_groups DROP FOREIGN KEY FK_DF562C08A76ED395");
        $this->addSql("ALTER TABLE platform DROP FOREIGN KEY FK_3952D0CBA76ED395");
        $this->addSql("ALTER TABLE payment_order DROP FOREIGN KEY FK_A260A52AA76ED395");
        $this->addSql("ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921FA76ED395");
        $this->addSql("ALTER TABLE withdrawal DROP FOREIGN KEY FK_6D2D3B45A76ED395");
        $this->addSql("ALTER TABLE platform_country DROP FOREIGN KEY FK_46430334FFE6496F");
        $this->addSql("ALTER TABLE platform_tag DROP FOREIGN KEY FK_B5AF4590FFE6496F");
        $this->addSql("ALTER TABLE platform_video DROP FOREIGN KEY FK_4E9BBEA8FFE6496F");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525FFE6496F");
        $this->addSql("ALTER TABLE platform DROP FOREIGN KEY FK_3952D0CBC54C8C93");
        $this->addSql("ALTER TABLE video_view_payment DROP FOREIGN KEY FK_E5868038E6F8F16A");
        $this->addSql("ALTER TABLE withdrawal DROP FOREIGN KEY FK_6D2D3B45712520F3");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E9295258BAC62AF");
        $this->addSql("ALTER TABLE campaign_country DROP FOREIGN KEY FK_FBC9554AF92F3E70");
        $this->addSql("ALTER TABLE platform_country DROP FOREIGN KEY FK_46430334F92F3E70");
        $this->addSql("ALTER TABLE video_views DROP FOREIGN KEY FK_9E929525F92F3E70");
        $this->addSql("ALTER TABLE net_city DROP FOREIGN KEY FK_5F22C447F92F3E70");
        $this->addSql("DROP TABLE credits");
        $this->addSql("DROP TABLE financial_transactions");
        $this->addSql("DROP TABLE payments");
        $this->addSql("DROP TABLE payment_instructions");
        $this->addSql("DROP TABLE age_range");
        $this->addSql("DROP TABLE campaign");
        $this->addSql("DROP TABLE campaign_country");
        $this->addSql("DROP TABLE campaign_tag");
        $this->addSql("DROP TABLE campaign_age_range");
        $this->addSql("DROP TABLE tag");
        $this->addSql("DROP TABLE vifeed_group");
        $this->addSql("DROP TABLE vifeed_user");
        $this->addSql("DROP TABLE vifeed_user_groups");
        $this->addSql("DROP TABLE platform");
        $this->addSql("DROP TABLE platform_country");
        $this->addSql("DROP TABLE platform_tag");
        $this->addSql("DROP TABLE platform_type");
        $this->addSql("DROP TABLE platform_video");
        $this->addSql("DROP TABLE video_views");
        $this->addSql("DROP TABLE payment_order");
        $this->addSql("DROP TABLE video_view_payment");
        $this->addSql("DROP TABLE wallet");
        $this->addSql("DROP TABLE withdrawal");
        $this->addSql("DROP TABLE net_city");
        $this->addSql("DROP TABLE net_country");
    }
}
