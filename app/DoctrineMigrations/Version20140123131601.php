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
        $this->addSql("CREATE TABLE `net_city` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) DEFAULT NULL,
  `name_ru` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `region` varchar(2) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `latitude` varchar(10) DEFAULT NULL,
  `longitude` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`),
  KEY `name_ru` (`name_ru`),
  KEY `name_en` (`name_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->addSql("CREATE TABLE `net_city_ip` (
    `city_id` int(11) DEFAULT NULL,
  `begin_ip` bigint(11) DEFAULT NULL,
  `end_ip` bigint(11) DEFAULT NULL,
  KEY `city_id` (`city_id`),
  KEY `ip` (`begin_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->addSql("CREATE TABLE `net_country` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ru` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `code` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `name_en` (`name_en`),
  KEY `name_ru` (`name_ru`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->addSql("CREATE TABLE `net_country_ip` (
    `country_id` int(11) DEFAULT '0',
  `begin_ip` bigint(11) DEFAULT NULL,
  `end_ip` bigint(11) DEFAULT '0',
  KEY `country_id` (`country_id`),
  KEY `ip` (`begin_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->addSql("CREATE TABLE `net_euro` (
    `country_id` int(11) DEFAULT '0',
  `begin_ip` bigint(11) DEFAULT NULL,
  `end_ip` bigint(11) DEFAULT '0',
  KEY `country_id` (`country_id`),
  KEY `ip` (`begin_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->addSql("CREATE TABLE `net_ru` (
    `city_id` int(11) DEFAULT '0',
  `begin_ip` bigint(11) DEFAULT NULL,
  `end_ip` bigint(11) DEFAULT NULL,
  KEY `city_id` (`city_id`),
  KEY `ip` (`begin_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $this->addSql("CREATE TABLE `vifeed_user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `type` enum('advertiser','publisher') COLLATE utf8_unicode_ci DEFAULT NULL,
  `vk_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `social_data` text COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `balance` decimal(11,2) NOT NULL,
  `email_confirmed` tinyint(1) NOT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notification` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BBEF2F5692FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_BBEF2F56A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `platform` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `discr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vk_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `hash_id` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3952D0CB3F7D58D2` (`hash_id`),
  KEY `IDX_3952D0CBA76ED395` (`user_id`),
  KEY `url_idx` (`url`),
  CONSTRAINT `FK_3952D0CBA76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `age_range` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `campaign` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8_unicode_ci DEFAULT NULL,
  `budget` decimal(8,2) NOT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `total_views` int(11) DEFAULT NULL,
  `bid` decimal(5,2) NOT NULL,
  `budget_used` decimal(8,2) NOT NULL,
  `status` enum('on','paused','ended','awaiting','archived') COLLATE utf8_unicode_ci NOT NULL,
  `daily_budget` decimal(8,2) NOT NULL,
  `daily_budget_used` decimal(8,2) NOT NULL,
  `hash_id` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `paid_views` int(11) DEFAULT NULL,
  `social_data` text COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `deleted_at` datetime DEFAULT NULL,
  `is_new` tinyint(1) NOT NULL,
  `youtube_data` text CHARACTER SET utf8 COMMENT '(DC2Type:array)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `balance` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1F1512DD3F7D58D2` (`hash_id`),
  KEY `IDX_1F1512DDA76ED395` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `FK_1F1512DDA76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `campaign_age_range` (
    `campaign_id` int(11) NOT NULL,
  `agerange_id` int(11) NOT NULL,
  PRIMARY KEY (`campaign_id`,`agerange_id`),
  KEY `IDX_C64440A4F639F774` (`campaign_id`),
  KEY `IDX_C64440A490B22C2E` (`agerange_id`),
  CONSTRAINT `FK_C64440A490B22C2E` FOREIGN KEY (`agerange_id`) REFERENCES `age_range` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C64440A4F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `campaign_ban` (
    `platform_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`platform_id`,`campaign_id`),
  KEY `IDX_5EB434A4F639F774` (`campaign_id`),
  KEY `IDX_5EB434A4FFE6496F` (`platform_id`),
  CONSTRAINT `FK_5EB434A4F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5EB434A4FFE6496F` FOREIGN KEY (`platform_id`) REFERENCES `platform` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `campaign_country` (
    `campaign_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  PRIMARY KEY (`campaign_id`,`country_id`),
  KEY `IDX_FBC9554AF639F774` (`campaign_id`),
  KEY `IDX_FBC9554AF92F3E70` (`country_id`),
  CONSTRAINT `FK_FBC9554AF639F774` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FBC9554AF92F3E70` FOREIGN KEY (`country_id`) REFERENCES `net_country` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `company` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `system` enum('ОСН','УСН') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `contact_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `position` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `inn` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `kpp` varchar(9) COLLATE utf8_unicode_ci NOT NULL,
  `bic` varchar(9) COLLATE utf8_unicode_ci NOT NULL,
  `bank_account` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `correspondent_account` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `is_approved` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4FBF094FA76ED395` (`user_id`),
  CONSTRAINT `FK_4FBF094FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `payment_instructions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` decimal(11,2) NOT NULL,
  `approved_amount` decimal(11,2) NOT NULL,
  `approving_amount` decimal(11,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `credited_amount` decimal(11,2) NOT NULL,
  `crediting_amount` decimal(11,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `deposited_amount` decimal(11,2) NOT NULL,
  `depositing_amount` decimal(11,2) NOT NULL,
  `extended_data` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:extended_payment_data)',
  `payment_system_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `reversing_approved_amount` decimal(11,2) NOT NULL,
  `reversing_credited_amount` decimal(11,2) NOT NULL,
  `reversing_deposited_amount` decimal(11,2) NOT NULL,
  `state` smallint(6) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_instruction_id` int(11) NOT NULL,
  `approved_amount` decimal(11,2) NOT NULL,
  `approving_amount` decimal(11,2) NOT NULL,
  `credited_amount` decimal(11,2) NOT NULL,
  `crediting_amount` decimal(11,2) NOT NULL,
  `deposited_amount` decimal(11,2) NOT NULL,
  `depositing_amount` decimal(11,2) NOT NULL,
  `expiration_date` datetime DEFAULT NULL,
  `reversing_approved_amount` decimal(11,2) NOT NULL,
  `reversing_credited_amount` decimal(11,2) NOT NULL,
  `reversing_deposited_amount` decimal(11,2) NOT NULL,
  `state` smallint(6) NOT NULL,
  `target_amount` decimal(11,2) NOT NULL,
  `attention_required` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_65D29B328789B572` (`payment_instruction_id`),
  CONSTRAINT `FK_65D29B328789B572` FOREIGN KEY (`payment_instruction_id`) REFERENCES `payment_instructions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `credits` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_instruction_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `attention_required` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `credited_amount` decimal(11,2) NOT NULL,
  `crediting_amount` decimal(11,2) NOT NULL,
  `reversing_amount` decimal(11,2) NOT NULL,
  `state` smallint(6) NOT NULL,
  `target_amount` decimal(11,2) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4117D17E8789B572` (`payment_instruction_id`),
  KEY `IDX_4117D17E4C3A3BB` (`payment_id`),
  CONSTRAINT `FK_4117D17E4C3A3BB` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4117D17E8789B572` FOREIGN KEY (`payment_instruction_id`) REFERENCES `payment_instructions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `financial_transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_id` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `extended_data` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:extended_payment_data)',
  `processed_amount` decimal(11,2) NOT NULL,
  `reason_code` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference_number` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `requested_amount` decimal(11,2) NOT NULL,
  `response_code` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` smallint(6) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `tracking_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_type` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1353F2D9CE062FF9` (`credit_id`),
  KEY `IDX_1353F2D94C3A3BB` (`payment_id`),
  CONSTRAINT `FK_1353F2D94C3A3BB` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1353F2D9CE062FF9` FOREIGN KEY (`credit_id`) REFERENCES `credits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

         $this->addSql("CREATE TABLE `payment_order` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `payment_instruction_id` int(11) DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `status` enum('new','pending','paid','cancelled') COLLATE utf8_unicode_ci NOT NULL,
  `bill_data` tinytext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A260A52A8789B572` (`payment_instruction_id`),
  KEY `IDX_A260A52AA76ED395` (`user_id`),
  CONSTRAINT `FK_A260A52A8789B572` FOREIGN KEY (`payment_instruction_id`) REFERENCES `payment_instructions` (`id`),
  CONSTRAINT `FK_A260A52AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


        $this->addSql("CREATE TABLE `platform_country` (
    `platform_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  PRIMARY KEY (`platform_id`,`country_id`),
  KEY `IDX_46430334FFE6496F` (`platform_id`),
  KEY `IDX_46430334F92F3E70` (`country_id`),
  CONSTRAINT `FK_46430334F92F3E70` FOREIGN KEY (`country_id`) REFERENCES `net_country` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_46430334FFE6496F` FOREIGN KEY (`platform_id`) REFERENCES `platform` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `referer_black_list` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `tag` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_389B7835E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `tagging` (
    `resource_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `resource_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`resource_type`,`resource_id`,`tag_id`),
  KEY `IDX_A4AED123BAD26311` (`tag_id`),
  CONSTRAINT `FK_A4AED123BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `video_views` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `current_time` smallint(6) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `track_number` smallint(6) NOT NULL,
  `ip` bigint(20) DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL,
  `viewer_id` char(43) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fingerprint` int(11) DEFAULT NULL,
  `is_in_stats` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9E929525FFE6496F` (`platform_id`),
  KEY `IDX_9E929525F639F774` (`campaign_id`),
  KEY `IDX_9E929525F92F3E70` (`country_id`),
  KEY `IDX_9E9295258BAC62AF` (`city_id`),
  KEY `ip` (`ip`),
  KEY `is_paid_viewer` (`viewer_id`,`campaign_id`,`is_paid`,`timestamp`,`is_in_stats`),
  CONSTRAINT `FK_9E9295258BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `net_city` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9E929525F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9E929525F92F3E70` FOREIGN KEY (`country_id`) REFERENCES `net_country` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9E929525FFE6496F` FOREIGN KEY (`platform_id`) REFERENCES `platform` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `video_view_payment` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_view_id` int(11) DEFAULT NULL,
  `charged` decimal(5,2) NOT NULL,
  `comission` decimal(5,2) NOT NULL,
  `paid` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E5868038E6F8F16A` (`video_view_id`),
  CONSTRAINT `FK_E5868038E6F8F16A` FOREIGN KEY (`video_view_id`) REFERENCES `video_views` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `vifeed_group` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E0FE35C95E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `vifeed_user_groups` (
    `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `IDX_DF562C08A76ED395` (`user_id`),
  KEY `IDX_DF562C08FE54D947` (`group_id`),
  CONSTRAINT `FK_DF562C08A76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`),
  CONSTRAINT `FK_DF562C08FE54D947` FOREIGN KEY (`group_id`) REFERENCES `vifeed_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `vifeed_user_ip_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `logged_at` datetime NOT NULL,
  `ip` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_ABC0A4A5A76ED395` (`user_id`),
  CONSTRAINT `FK_ABC0A4A5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `wallet` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('yandex','wm','qiwi') COLLATE utf8_unicode_ci NOT NULL,
  `number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7C68921FA76ED395` (`user_id`),
  CONSTRAINT `FK_7C68921FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->addSql("CREATE TABLE `withdrawal` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `wallet_id` int(11) NOT NULL,
  `amount` decimal(9,2) NOT NULL,
  `status` enum('new','ok','error','cancelled') COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6D2D3B45A76ED395` (`user_id`),
  KEY `IDX_6D2D3B45712520F3` (`wallet_id`),
  CONSTRAINT `FK_6D2D3B45712520F3` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6D2D3B45A76ED395` FOREIGN KEY (`user_id`) REFERENCES `vifeed_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

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
