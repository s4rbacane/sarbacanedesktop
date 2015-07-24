<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Sarbacane Software <contact@sarbacane.com>
 *  @copyright 2015 Sarbacane Software
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_'))
	exit;
function upgrade_module_1_0_6($object)
{
	//sql update
	$update1 = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop`');
	$update2 = Db::getInstance()->execute('CREATE TABLE `'._DB_PREFIX_.'sd_updates` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`update_date` DATETIME NOT NULL,
		`customer_id` INT(11) NULL DEFAULT NULL,
		`customer_email` VARCHAR(255) NULL DEFAULT NULL COLLATE \'utf8_bin\',
		`action` VARCHAR(10) NOT NULL COLLATE \'utf8_bin\',
		PRIMARY KEY (`id`)) COLLATE=\'utf8_bin\' ENGINE='._MYSQL_ENGINE_);
	$update3 = Db::getInstance()->execute('
		ALTER TABLE `'._DB_PREFIX_.'sarbacanedesktop_users`
		ADD COLUMN `list_id` varchar(50) NULL AFTER `sd_id`,
		ADD COLUMN `last_call_date` DATETIME NULL AFTER `list_id`;	
	');
	return $update1 && $update2 && $update3;
}
