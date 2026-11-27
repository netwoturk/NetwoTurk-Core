<?php
class NtcrInstaller
{
    public static function install($module)
    {
        if (!extension_loaded('bcmath') || !class_exists('ZipArchive')) {
            return false;
        }
        foreach(self::sql() as $sql) if(!Db::getInstance()->execute($sql)) return false;
        Configuration::updateValue('NTCR_NOTIFY_EMAIL','destek@netwoturk.com');
        Configuration::updateValue('NTCR_RATE','0.02000000');
        Configuration::updateValue('NTCR_LICENSE_API','https://netwoturktech.com/module/ntlicensemanager/');
        return self::tabs($module);
    }
    public static function uninstall($module)
    {
        foreach(array('AdminNtCreatorRewards','AdminNtCreatorRewardsImport','AdminNtCreatorRewardsPublishers','AdminNtCreatorRewardsRequests','AdminNtCreatorRewardsLicense') as $class){$id=(int)Tab::getIdFromClassName($class);if($id)(new Tab($id))->delete();}
        return true; // Finansal ve talep geçmişi bilinçli olarak korunur.
    }
    private static function tabs($m)
    {
        $parent=new Tab();$parent->active=1;$parent->class_name='AdminNtCreatorRewards';$parent->module=$m->name;$parent->id_parent=0;$parent->icon='redeem';
        foreach(Language::getLanguages(false) as $l)$parent->name[$l['id_lang']]='Yayıncı Ödülleri'; if(!$parent->add())return false;
        $items=array('AdminNtCreatorRewardsImport'=>'Rapor Aktarımı','AdminNtCreatorRewardsPublishers'=>'Yayıncılar','AdminNtCreatorRewardsRequests'=>'Talepler','AdminNtCreatorRewardsLicense'=>'Lisans');
        foreach($items as $c=>$n){$t=new Tab();$t->active=1;$t->class_name=$c;$t->module=$m->name;$t->id_parent=(int)$parent->id;foreach(Language::getLanguages(false) as $l)$t->name[$l['id_lang']]=$n;if(!$t->add())return false;} return true;
    }
    private static function sql()
    {
        $p=_DB_PREFIX_;$e=_MYSQL_ENGINE_; return array(
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_publisher` (`id_publisher` INT UNSIGNED AUTO_INCREMENT,`id_shop` INT UNSIGNED NOT NULL,`id_customer` INT UNSIGNED NULL,`creator_id` VARCHAR(64) NULL,`handle` VARCHAR(64) NULL,`pending_handle` VARCHAR(64) NULL,`display_name` VARCHAR(190) NULL,`status` ENUM('pending','verified','conflict','inactive') NOT NULL DEFAULT 'pending',`cash_allowed` TINYINT(1) NOT NULL DEFAULT 0,`date_add` DATETIME NOT NULL,`date_upd` DATETIME NOT NULL,PRIMARY KEY(`id_publisher`),UNIQUE KEY `uq_customer`(`id_shop`,`id_customer`),KEY `idx_creator`(`id_shop`,`creator_id`),KEY `idx_handle`(`id_shop`,`handle`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_batch` (`id_batch` INT UNSIGNED AUTO_INCREMENT,`id_shop` INT UNSIGNED NOT NULL,`period` CHAR(6) NOT NULL,`file_hash` CHAR(64) NOT NULL,`source_name` VARCHAR(190) NOT NULL,`fx_rate` DECIMAL(20,8) NULL,`share_rate` DECIMAL(12,8) NOT NULL DEFAULT 0.02000000,`status` ENUM('draft','approved','failed') NOT NULL DEFAULT 'draft',`row_count` INT UNSIGNED NOT NULL,`source_total_usd` DECIMAL(24,8) NOT NULL,`id_employee` INT UNSIGNED NOT NULL,`date_add` DATETIME NOT NULL,`date_approved` DATETIME NULL,PRIMARY KEY(`id_batch`),UNIQUE KEY `uq_hash_shop`(`id_shop`,`file_hash`,`period`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_source_row` (`id_source_row` BIGINT UNSIGNED AUTO_INCREMENT,`id_batch` INT UNSIGNED NOT NULL,`source_row_no` INT UNSIGNED NOT NULL,`period` CHAR(6) NOT NULL,`creator_id` VARCHAR(64) NULL,`handle` VARCHAR(64) NULL,`nickname` VARCHAR(190) NULL,`diamonds` DECIMAL(24,4) NULL,`valid_days` DECIMAL(12,4) NULL,`live_hours` DECIMAL(16,6) NULL,`incentive_usd` DECIMAL(24,8) NULL,`id_publisher` INT UNSIGNED NULL,`validation_status` VARCHAR(32) NOT NULL,`validation_message` VARCHAR(255) NULL,`target_points` BIGINT UNSIGNED NULL,PRIMARY KEY(`id_source_row`),UNIQUE KEY `uq_source`(`id_batch`,`source_row_no`),KEY `idx_match`(`id_batch`,`id_publisher`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_wallet` (`id_publisher` INT UNSIGNED NOT NULL,`available_points` BIGINT NOT NULL DEFAULT 0,`reserved_points` BIGINT UNSIGNED NOT NULL DEFAULT 0,`debt_points` BIGINT UNSIGNED NOT NULL DEFAULT 0,`date_upd` DATETIME NOT NULL,PRIMARY KEY(`id_publisher`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_ledger` (`id_ledger` BIGINT UNSIGNED AUTO_INCREMENT,`id_shop` INT UNSIGNED NOT NULL,`id_publisher` INT UNSIGNED NOT NULL,`period` CHAR(6) NULL,`type` VARCHAR(32) NOT NULL,`points` BIGINT NOT NULL,`idempotency_key` VARCHAR(190) NOT NULL,`reference_type` VARCHAR(32) NULL,`reference_id` BIGINT UNSIGNED NULL,`description` VARCHAR(255) NOT NULL,`id_employee` INT UNSIGNED NULL,`date_add` DATETIME NOT NULL,PRIMARY KEY(`id_ledger`),UNIQUE KEY `uq_idem`(`idempotency_key`),KEY `idx_wallet`(`id_publisher`,`date_add`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_period_entitlement` (`id_shop` INT UNSIGNED NOT NULL,`id_publisher` INT UNSIGNED NOT NULL,`period` CHAR(6) NOT NULL,`net_points` BIGINT NOT NULL DEFAULT 0,`last_batch_id` INT UNSIGNED NOT NULL,`date_upd` DATETIME NOT NULL,PRIMARY KEY(`id_shop`,`id_publisher`,`period`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_request` (`id_request` BIGINT UNSIGNED AUTO_INCREMENT,`request_no` VARCHAR(32) NOT NULL,`id_shop` INT UNSIGNED NOT NULL,`id_publisher` INT UNSIGNED NOT NULL,`type` ENUM('gift','explore','cash') NOT NULL,`status` VARCHAR(32) NOT NULL DEFAULT 'pending',`points` BIGINT UNSIGNED NOT NULL,`snapshot_json` LONGTEXT NOT NULL,`public_note` TEXT NULL,`private_note` TEXT NULL,`date_add` DATETIME NOT NULL,`date_upd` DATETIME NOT NULL,PRIMARY KEY(`id_request`),UNIQUE KEY `uq_no`(`request_no`),KEY `idx_owner`(`id_publisher`,`date_add`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `{$p}ntcr_audit` (`id_audit` BIGINT UNSIGNED AUTO_INCREMENT,`id_shop` INT UNSIGNED NOT NULL,`actor_type` VARCHAR(16) NOT NULL,`actor_id` INT UNSIGNED NOT NULL,`action` VARCHAR(64) NOT NULL,`entity_type` VARCHAR(32) NOT NULL,`entity_id` BIGINT UNSIGNED NULL,`payload_json` LONGTEXT NULL,`date_add` DATETIME NOT NULL,PRIMARY KEY(`id_audit`),KEY `idx_entity`(`entity_type`,`entity_id`)) ENGINE={$e} DEFAULT CHARSET=utf8mb4"
        );
    }
}
