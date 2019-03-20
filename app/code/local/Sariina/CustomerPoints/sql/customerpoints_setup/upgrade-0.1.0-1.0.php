<?php 
/**
 * @name         :  Sariina Aasud
 * @version      :  0.0.1
 * @since        :  Magento 1.9.1
 * @author       :  Sariina - http://www.sariina.com
 * @copyright    :  Copyright (C) 2015 Powered by Sariina
 * @license      :  This source file is subject to the EULA that is bundled with this package in the file SARIINA-LICENSE.txt.
 * @Creation Date:  Apr 29 2015
 **/

$installer = $this;
$installer->startSetup();

$firstname = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'firstname');
$lastname = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'lastname');

$installer->run(<<<SQL
ALTER TABLE `{$this->getTable('sariina_customerpoints/pointables')}`
    ADD COLUMN `option_id` int unsigned DEFAULT NULL AFTER `total_points`,
    ADD FOREIGN KEY `fk_customerpoints_pointables_option_id` (`option_id`) REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`);
SQL
);

$installer->run(<<<SQL
ALTER TABLE `{$this->getTable('sariina_customerpoints/history')}`
    ADD COLUMN `final_price` decimal(12,4) DEFAULT NULL AFTER `points`,
    ADD COLUMN `item_id` int unsigned DEFAULT NULL AFTER `customer_id`,
    ADD FOREIGN KEY `fk_customerpoints_history_item_id` (`item_id`) REFERENCES `{$this->getTable('sales/order_item')}` (`item_id`);
SQL
);
// This view helps us in gathering aggregate data about each customer's total points
// in separate leagues of all times
$installer->run(<<<SQL
    CREATE OR REPLACE SQL SECURITY INVOKER VIEW `sariina_customerpoints_leagues_view` AS SELECT STRAIGHT_JOIN
        `historyTable`.`id`,
        `historyTable`.`customer_id`,
        `historyTable`.`created_at`,
        `historyTable`.`points`,
        `historyTable`.`final_price`,
        `historyTable`.`item_id`,
        `pointablesTable`.`total_points`,
        `pointablesTable`.`option_id`,
        `customerTableOne`.`value` AS `firstname`,
        `customerTableTwo`.`value` AS `lastname`,
        CONCAT_WS(' ', `customerTableOne`.`value`, `customerTableTwo`.`value`) AS `customer_name`,
        `leagues`.`date_name`,
        `leagues`.`league_id`
    FROM `{$this->getTable('sariina_customerpoints/leagues')}` AS `leagues`
        INNER JOIN `{$this->getTable('sariina_customerpoints/pointables')}` AS `pointablesTable` ON `leagues`.`league_id` = `pointablesTable`.`pointable_id` AND `pointablesTable`.`pointable_type` = 'League'
        INNER JOIN `{$this->getTable('sariina_customerpoints/history')}` AS `historyTable` ON `pointablesTable`.`point_id` = `historyTable`.`id`
        INNER JOIN `{$firstname->getBackendTable()}` AS `customerTableOne` ON `customerTableOne`.`entity_id` = `historyTable`.`customer_id`
        INNER JOIN `{$lastname->getBackendTable()}` AS `customerTableTwo` ON `customerTableTwo`.`entity_id` = `historyTable`.`customer_id` 
    WHERE
        `customerTableOne`.`attribute_id` = {$firstname->getAttributeId()} AND
        `customerTableTwo`.`attribute_id` = {$lastname->getAttributeId()};
SQL
);


// This table will hold default dedicated amount or percent to each option
$installer->run(<<<SQL
    CREATE TABLE IF NOT EXISTS `sariina_customerpoints_options` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `option_id` int(10) unsigned NOT NULL UNIQUE,
        `percent` decimal(4,2) NOT NULL,
        `amount` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
);

// We need a table to store winners at the end of each league
$installer->run(<<<SQL
    CREATE TABLE IF NOT EXISTS `sariina_customerpoints_winners` (
        `id` int NOT NULL AUTO_INCREMENT,
        `option_id` int NOT NULL,
        `league_id` int NOT NULL,
        `customer_id` int NOT NULL,
        `amount` int NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY (`customer_id`, `option_id`, `league_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
);

// We are assigning a new attribute to customers
// This allows them to use a nickname in place of their real name
$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer', 'nickname', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Nickname',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'default' => '',
    'visible_on_front' => 1,
    'source' =>   NULL,
    'comment' => 'Store a customer nickname'
));

$setup->endSetup();
$installer->endSetup();