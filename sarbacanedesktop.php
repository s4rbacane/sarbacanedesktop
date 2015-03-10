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

class Sarbacanedesktop extends Module
{

	public function __construct()
	{
		$this->version = '1.0.4';
		$this->name = 'sarbacanedesktop';
		$this->tab = 'emailing';
		$this->author = 'Sarbacane Software';
		$this->need_instance = 1;
		$this->bootstrap = true;
		parent::__construct();
		$this->displayName = $this->l('Sarbacane Desktop');
		$this->description = $this->l('Automatically synchronize clients,');
		$this->description .= ' '.$this->l('accounts and subscribers to your e-shop newsletter with Sarbacane Desktop\'s email marketing software;');
		$this->description .= ' '.$this->l('create and send stunning newsletters and email marketing campaigns.');
	}

	public function install()
	{
		if (!$this->checkPrestashopVersion())
			return false;
		$result1 = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop`');
		$result2 = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop_users`');
		$result3 = Db::getInstance()->execute('
		CREATE TABLE `'._DB_PREFIX_.'sarbacanedesktop` (
			`email` varchar(150) NOT NULL,
			`list_type` varchar(20) NOT NULL,
			`id_shop` varchar(20) NOT NULL,
			`id_sd_id` varchar(20) NOT NULL,
			`customer_data` varchar(200) NOT NULL,
			PRIMARY KEY (`email`, `list_type`, `id_shop`, `id_sd_id`),
			INDEX `sd` (`list_type`, `id_shop`, `id_sd_id`, `customer_data`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
		$result4 = Db::getInstance()->execute('
		CREATE TABLE `'._DB_PREFIX_.'sarbacanedesktop_users` (
			`id_sd_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
			`sd_id` varchar(200) NOT NULL,
			PRIMARY KEY (`id_sd_id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
		$result5 = Configuration::updateGlobalValue('SARBACANEDESKTOP_TOKEN', '');
		$result6 = Configuration::updateGlobalValue('SARBACANEDESKTOP_LIST', '');
		$result7 = Configuration::updateGlobalValue('SARBACANEDESKTOP_IS_USER', '');
		if (!$result1 || !$result2 || !$result3 || !$result4 || !$result5 || !$result6 || !$result7)
			return false;
		if (!parent::install())
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		if (!Configuration::deleteByName('SARBACANEDESKTOP_TOKEN')
		|| !Configuration::deleteByName('SARBACANEDESKTOP_LIST')
		|| !Configuration::deleteByName('SARBACANEDESKTOP_IS_USER'))
			return false;
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop`');
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop_users`');
		return true;
	}

	public function initSynchronisation()
	{
		$content = '';
		if (Module::isInstalled($this->name) && Tools::getIsset('stk') && Tools::getIsset('sdid') && $this->checkPrestashopVersion())
		{
			if (Tools::getValue('stk') == $this->getToken())
			{
				$sdid = Tools::getvalue('sdid');
				if ($sdid != '' && $this->getConfiguration('nb_configured') == 3)
				{
					$id_sd_id = $this->getSdidIdentifier($sdid);
					if ($id_sd_id == '' && !Tools::getIsset('list') && !Tools::getIsset('action'))
						$id_sd_id = $this->saveSdid($sdid);
					if ($id_sd_id != '')
					{
						$sd_list_array = $this->getListConfiguration('array');
						if (Configuration::getGlobalValue('SARBACANEDESKTOP_TOKEN') != ''
						&& Configuration::getGlobalValue('SARBACANEDESKTOP_LIST') != ''
						&& is_array($sd_list_array) && count($sd_list_array) > 0)
						{
							ini_set('max_execution_time', 1200);
							if (Tools::getIsset('list'))
							{
								$list = Tools::getValue('list');
								$id_shop = $this->getStoreidFromList($list);
								$list_type = $this->getListTypeFromList($list);
								$list_type_array = $this->getListTypeArray();
								if (in_array($list_type, $list_type_array))
								{
									$id_and_list = $id_shop.$list_type;
									if (($list_type == 'N' && in_array($id_and_list.'0', $sd_list_array))
									|| ($list_type == 'C' && (in_array($id_and_list.'0', $sd_list_array) || in_array($id_and_list.'1', $sd_list_array))))
									{
										if (Tools::getIsset('action') && Tools::getValue('action') == 'reset')
											$this->resetList($list_type, $id_shop, $id_sd_id);
										$content = $this->processNewUnsubcribersAndSubscribers($list_type, $id_shop, $id_sd_id);
									}
								}
							}
							else
							{
								if (Tools::getIsset('action') && Tools::getValue('action') == 'delete')
									$this->deleteSdUser($id_sd_id);
								else
									$content = $this->getFormattedContentShops($id_sd_id);
							}
						}
					}
				}
			}
		}
		return $content;
	}

	private function checkPrestashopVersion()
	{
		if (version_compare(_PS_VERSION_, '1.5.0.9', '<'))
			return false;
		return true;
	}

	private function getToken()
	{
		$str = Configuration::getGlobalValue('SARBACANEDESKTOP_TOKEN');
		$str = $str.Tools::substr(Tools::encrypt('SecurityTokenForModule'), 0, 11).$str;
		$str = Tools::encrypt($str);
		return $str;
	}

	private function processNewUnsubcribersAndSubscribers($list_type, $id_shop, $id_sd_id)
	{
		$content = 'email;lastname;firstname';
		if ($list_type == 'C')
		{
			$content .= ';partners';
			if ($this->checkIfListWithCustomerData($list_type, $id_shop))
				$content .= ';date_first_order;date_last_order;amount_min_order;amount_max_order;amount_avg_order;nb_orders;amount_all_orders';
		}
		$content .= ';action';
		$content .= "\n";
		$content .= $this->processNewUnsubscribers($list_type, $id_shop, $id_sd_id);
		$content .= $this->processNewSubscribers($list_type, $id_shop, $id_sd_id);
		return $content;
	}

	private function getStoreidFromList($list)
	{
		if (Tools::substr($list, -1) == 'N' || Tools::substr($list, -1) == 'C')
			return Tools::substr($list, 0, -1);
		else
			return Tools::substr($list, 0, -2);
	}

	private function getListTypeFromList($list)
	{
		if (Tools::substr($list, -1) == 'N' || Tools::substr($list, -1) == 'C')
			return Tools::substr($list, -1);
		else
			return Tools::substr($list, -2, 1);
	}

	private function checkIfListWithCustomerData($list_type, $id_shop)
	{
		$sd_list_array = $this->getListConfiguration('array');
		if (in_array($id_shop.$list_type.'1', $sd_list_array))
			return true;
		return false;
	}

	private function getFormattedContentShops($id_sd_id)
	{
		$stores = $this->getStoresArray();
		$content = 'list_id;name;reset;is_updated;type;version'."\n";
		$sd_list_array = $this->getListConfiguration('array');
		$list_array = array();
		foreach ($sd_list_array as $list)
		{
			$id_shop = $this->getStoreidFromList($list);
			$list_type = $this->getListTypeFromList($list);
			array_push($list_array, array('id_shop' => $id_shop, 'list_type' => $list_type));
		}
		foreach ($stores as $store)
		{
			foreach ($list_array as $list)
			{
				if ($store['id_shop'] == $list['id_shop'])
				{
					$store_list = $store['id_shop'].$list['list_type'].';'.$this->dQuote($store['name']).';';
					$store_list .= $this->listIsResetted($store['id_shop'], $list['list_type'], $id_sd_id).';';
					$store_list .= $this->listIsUpdated($store['id_shop'], $list['list_type'], $id_sd_id).';';
					$store_list .= 'Prestashop;'.$this->version."\n";
					$content .= $store_list;
				}
			}
		}
		return $content;
	}

	private function listIsResetted($id_shop, $list_type, $id_sd_id)
	{
		$rq_sql = '
		SELECT count(`email`) AS `nb_in_table`
		FROM `'._DB_PREFIX_.'sarbacanedesktop`
		WHERE `list_type` = \''.pSql($list_type).'\'
		AND `id_shop` = \''.pSql($id_shop).'\'
		AND `id_sd_id` = \''.pSql($id_sd_id).'\'';
		$nb_in_table = Db::getInstance()->getValue($rq_sql);
		if ($nb_in_table == 0)
			return 'Y';
		return 'N';
	}

	private function listIsUpdated($id_shop, $list_type, $id_sd_id)
	{
		$is_updated_list = 'N';
		if ($this->processNewUnsubscribers($list_type, $id_shop, $id_sd_id, 'is_updated') > 0)
			$is_updated_list = 'Y';
		if ($this->processNewSubscribers($list_type, $id_shop, $id_sd_id, 'is_updated') > 0)
			$is_updated_list = 'Y';
		return $is_updated_list;
	}

	private function dQuote($value)
	{
		$value = str_replace('"', '""', $value);
		if (strpos($value, ' ') || strpos($value, ';'))
			$value = '"'.$value.'"';
		return $value;
	}

	private function getStoresArray()
	{
		$rq_sql = '
		SELECT `id_shop`, `name`
		FROM `'._DB_PREFIX_.'shop`
		ORDER BY `id_shop` ASC';
		$rq = Db::getInstance()->executeS($rq_sql);
		$stores_array = array();
		if (is_array($rq))
		{
			foreach ($rq as $r)
				$stores_array[] = array('id_shop' => $r['id_shop'], 'name' => $r['name']);
		}
		return $stores_array;
	}

	private function getListTypeArray()
	{
		return array('N', 'C');
	}

	private function checkIfNewsletterModule($id_shop)
	{
		$rq_sql = '
		SELECT count(*)
		FROM `'._DB_PREFIX_.'module` m,
		`'._DB_PREFIX_.'module_shop` ms
		WHERE m.`name` = \'blocknewsletter\'
		AND m.`active` = 1
		AND m.`id_module` = ms.`id_module`
		AND ms.`id_shop` = '.(int)$id_shop;
		$block_newsletter = Db::getInstance()->getValue($rq_sql);
		if ($block_newsletter == 1)
		{
			$rq = Db::getInstance()->executeS($rq_sql);
			$rq_sql = 'SHOW TABLES LIKE \''._DB_PREFIX_.'newsletter\'';
			$rq = Db::getInstance()->executeS($rq_sql);
			if (count($rq) > 0)
			{
				$rq_sql = '
				SELECT *
				FROM `'._DB_PREFIX_.'newsletter`
				LIMIT 0, 1';
				$rq = Db::getInstance()->executeS($rq_sql);
				if (is_array($rq))
				{
					foreach ($rq as $r)
					{
						if (isset($r['email']) && isset($r['id_shop']) && isset($r['active']))
							return true;
					}
				}
			}
		}
		return false;
	}

	private function getShopCustomerSelection($id_shop)
	{
		if (version_compare(_PS_VERSION_, '1.5.0.9', '='))
		{
			$rq_sql = '
			SELECT gs.`id_group_shop`
			FROM `'._DB_PREFIX_.'shop` s,
			`'._DB_PREFIX_.'group_shop` gs
			WHERE s.`id_group_shop` = gs.`id_group_shop`
			AND s.`id_shop` = '.(int)$id_shop.'
			AND gs.`share_customer` = 1';
		}
		else
		{
			$rq_sql = '
			SELECT sg.`id_shop_group`
			FROM `'._DB_PREFIX_.'shop` s,
			`'._DB_PREFIX_.'shop_group` sg
			WHERE s.`id_shop_group` = sg.`id_shop_group`
			AND s.`id_shop` = '.(int)$id_shop.'
			AND sg.`share_customer` = 1';
		}
		$rq = Db::getInstance()->executeS($rq_sql);
		if (is_array($rq))
		{
			foreach ($rq as $r)
			{
				if (version_compare(_PS_VERSION_, '1.5.0.9', '='))
					return '`id_group_shop` = '.(int)$r['id_group_shop'];
				else
					return '`id_shop_group` = '.(int)$r['id_shop_group'];
			}
		}
		return '`id_shop` = '.(int)$id_shop;
	}

	private function processNewSubscribers($list_type, $id_shop, $id_sd_id, $type_action = 'display')
	{
		$check_if_newsletter_module = $this->checkIfNewsletterModule($id_shop);
		$shop_customer_selection = $this->getShopCustomerSelection($id_shop);
		$rq_sql_limit = '';
		if ($type_action == 'is_updated')
			$rq_sql_limit = 'LIMIT 0, 1';
		else if (Tools::getIsset('limit') && Tools::getValue('limit') == 'true')
			$rq_sql_limit = 'LIMIT 0, 20000';
		if ($list_type == 'N')
		{
			$rq_sql = '
			SELECT t.* FROM (
				(
					SELECT c.`email`, c.`lastname`, c.`firstname`
					FROM `'._DB_PREFIX_.'customer` c
					WHERE c.'.$shop_customer_selection.'
					AND c.`deleted` = 0
					AND c.`newsletter` = 1
					AND c.`id_customer` = (
						SELECT cu.`id_customer`
						FROM `'._DB_PREFIX_.'customer` cu
						WHERE cu.'.$shop_customer_selection.'
						AND cu.`email` = c.`email`
						AND cu.`deleted` = 0
						AND cu.`newsletter` = 1
						ORDER BY cu.`is_guest` ASC, cu.`id_customer` DESC
						LIMIT 0, 1
					)
				)';
				if ($check_if_newsletter_module)
				{
					$rq_sql .= '
					UNION ALL (
						SELECT n.`email`, \'\' AS `lastname`, \'\' AS `firstname`
						FROM `'._DB_PREFIX_.'newsletter` n
						WHERE n.`id_shop` = '.(int)$id_shop.'
						AND n.`active` = 1
						AND n.`email` NOT IN (
							SELECT cus.`email`
							FROM `'._DB_PREFIX_.'customer` cus
							WHERE cus.'.$shop_customer_selection.'
							AND cus.`deleted` = 0
							AND cus.`newsletter` = 1
						)
					)';
				}
			$rq_sql .= '
			) AS `t`
			WHERE t.`email` NOT IN (
				SELECT s.`email`
				FROM `'._DB_PREFIX_.'sarbacanedesktop` s
				WHERE s.`list_type` = \'N\'
				AND s.`id_shop` = \''.pSql($id_shop).'\'
				AND s.`id_sd_id` = \''.pSql($id_sd_id).'\'
				AND s.`customer_data` = CONCAT(t.`lastname`, \'_\', t.`firstname`)
			)
			'.$rq_sql_limit;
		}
		else if ($list_type == 'C')
		{
			$add_customer_data = $this->checkIfListWithCustomerData($list_type, $id_shop);
			$rq_sql = '
			SELECT t.* FROM (
				SELECT c.`email`, c.`lastname`, c.`firstname`, c.`optin`';
				if ($add_customer_data)
				{
					$rq_sql .= ',
					IFNULL(MIN(o.`date_add`), \'\') AS `date_first_order`, IFNULL(MAX(o.`date_add`), \'\') AS `date_last_order`,
					IFNULL(MIN(o.`total_paid_tax_incl`), \'\') AS `amount_min_order`, IFNULL(MAX(o.`total_paid_tax_incl`), \'\') AS `amount_max_order`,
					IFNULL(ROUND(AVG(o.`total_paid_tax_incl`), 6), \'\') AS `amount_avg_order`,
					IFNULL(SUM(o.`one_order`), \'\') AS `nb_orders`, IFNULL(SUM(o.`total_paid_tax_incl`), \'\') AS `amount_all_orders`';
				}
				$rq_sql .= '
				FROM `'._DB_PREFIX_.'customer` c';
				if ($add_customer_data)
				{
					$rq_sql .= '
					LEFT JOIN (
						SELECT o.`total_paid_tax_incl`, o.`date_add`, cus.`email`, 1 AS `one_order`
						FROM `'._DB_PREFIX_.'orders` o,
						`'._DB_PREFIX_.'customer` cus
						WHERE o.`id_shop` = '.(int)$id_shop.'
						AND o.`id_customer` = cus.`id_customer`
					) AS o ON o.`email` = c.`email`';
				}
				$rq_sql .= '
				WHERE c.'.$shop_customer_selection.'
				AND c.`deleted` = 0
				AND c.`id_customer` = (
					SELECT cu.`id_customer`
					FROM `'._DB_PREFIX_.'customer` cu
					WHERE cu.'.$shop_customer_selection.'
					AND cu.`email` = c.`email`
					AND cu.`deleted` = 0
					ORDER BY cu.`is_guest` ASC, cu.`id_customer` DESC
					LIMIT 0, 1
				)
				GROUP BY c.`email`
			) AS `t`
			WHERE t.`email` NOT IN (
				SELECT s.`email`
				FROM `'._DB_PREFIX_.'sarbacanedesktop` s
				WHERE s.`list_type` = \'C\'
				AND s.`id_shop` = \''.pSql($id_shop).'\'
				AND s.`id_sd_id` = \''.pSql($id_sd_id).'\'';
				if ($add_customer_data)
				{
					$rq_sql .= '
					AND s.`customer_data` = CONCAT(
						t.`lastname`, \'_\', t.`firstname`, t.`optin`, t.`amount_min_order`, t.`amount_max_order`, t.`nb_orders`, t.`amount_all_orders`
					)';
				}
				else
				{
					$rq_sql .= '
					AND s.`customer_data` = CONCAT(t.`lastname`, \'_\', t.`firstname`, t.`optin`)';
				}
			$rq_sql .= '
			)
			'.$rq_sql_limit;
		}
		else
			return array();
		$rq = Db::getInstance()->query($rq_sql);
		$rq_sql_insert = '';
		$i = 0;
		$content = '';
		while ($r = Db::getInstance()->nextRow($rq))
		{
			if ($type_action == 'is_updated')
				return 1;
			$content .= $this->dQuote($r['email']).';';
			$content .= $this->dQuote($r['lastname']).';'.$this->dQuote($r['firstname']);
			if ($list_type == 'C')
			{
				$content .= ';'.$r['optin'];
				if ($add_customer_data)
				{
					$content .= ';'.$this->dQuote($r['date_first_order']).';'.$this->dQuote($r['date_last_order']);
					$content .= ';'.(float)$r['amount_min_order'].';'.(float)$r['amount_max_order'].';'.(float)$r['amount_avg_order'];
					$content .= ';'.(int)$r['nb_orders'].';'.(float)$r['amount_all_orders'];
				}
			}
			$content .= ';S'."\n";
			$customer_data = $r['lastname'].'_'.$r['firstname'];
			if ($list_type == 'C')
			{
				$customer_data .= $r['optin'];
				if ($add_customer_data)
					$customer_data .= $r['amount_min_order'].$r['amount_max_order'].$r['nb_orders'].$r['amount_all_orders'];
			}
			$insert_values = '\''.pSql($r['email']).'\',\''.pSql($list_type).'\',\''.pSql($id_shop).'\',\''.pSql($id_sd_id).'\',\''.pSql($customer_data).'\'';
			$rq_sql_insert .= ' ('.$insert_values.'),';
			if ($i == 1000)
			{
				$this->addedNewSubscribers($rq_sql_insert);
				$rq_sql_insert = '';
				$i = 0;
			}
			$i++;
		}
		if ($type_action == 'is_updated')
			return 0;
		if ($rq_sql_insert != '')
			$this->addedNewSubscribers($rq_sql_insert);
		return $content;
	}

	private function addedNewSubscribers($rq_sql_insert)
	{
		$rq_sql_insert = Tools::substr($rq_sql_insert, 0, -1);
		$rq_sql = '
		INSERT INTO `'._DB_PREFIX_.'sarbacanedesktop` (`email`, `list_type`, `id_shop`, `id_sd_id`, `customer_data`) VALUES
		'.$rq_sql_insert.'
		ON DUPLICATE KEY UPDATE
		`customer_data` = VALUES(`customer_data`)';
		Db::getInstance()->execute($rq_sql);
	}

	private function processNewUnsubscribers($list_type, $id_shop, $id_sd_id, $type_action = 'display')
	{
		$check_if_newsletter_module = $this->checkIfNewsletterModule($id_shop);
		$shop_customer_selection = $this->getShopCustomerSelection($id_shop);
		$rq_sql_limit = '';
		if ($type_action == 'is_updated')
			$rq_sql_limit = 'LIMIT 0, 1';
		else if (Tools::getIsset('limit') && Tools::getValue('limit') == 'true')
			$rq_sql_limit = 'LIMIT 0, 20000';
		if ($list_type == 'N')
		{
			$rq_sql = '
			SELECT s.`email`
			FROM `'._DB_PREFIX_.'sarbacanedesktop` s
			WHERE s.`email` NOT IN (
				SELECT c.`email`
				FROM `'._DB_PREFIX_.'customer` c
				WHERE c.'.$shop_customer_selection.'
				AND c.`deleted` = 0
				AND c.`newsletter` = 1
			)';
			if ($check_if_newsletter_module)
			{
				$rq_sql .= '
				AND s.`email` NOT IN (
					SELECT n.`email`
					FROM `'._DB_PREFIX_.'newsletter` n
					WHERE n.`id_shop` = '.(int)$id_shop.'
					AND n.`active` = 1
				)';
			}
			$rq_sql .= '
			AND s.`list_type` = \'N\'
			AND s.`id_shop` = \''.pSql($id_shop).'\'
			AND s.`id_sd_id` = \''.pSql($id_sd_id).'\'
			'.$rq_sql_limit;
		}
		else if ($list_type == 'C')
		{
			$rq_sql = '
			SELECT s.`email`
			FROM `'._DB_PREFIX_.'sarbacanedesktop` s
			WHERE s.`email` NOT IN (
				SELECT c.`email`
				FROM `'._DB_PREFIX_.'customer` c
				WHERE c.'.$shop_customer_selection.'
				AND c.`deleted` = 0
			)
			AND s.`list_type` = \'C\'
			AND s.`id_shop` = \''.pSql($id_shop).'\'
			AND s.`id_sd_id` = \''.pSql($id_sd_id).'\'
			'.$rq_sql_limit;
		}
		else
			return;
		$rq = Db::getInstance()->query($rq_sql);
		$rq_sql_delete = '';
		$i = 0;
		$content = '';
		while ($r = Db::getInstance()->nextRow($rq))
		{
			if ($type_action == 'is_updated')
				return 1;
			$content .= $this->dQuote($r['email']).';;';
			if ($list_type == 'C')
			{
				$content .= ';';
				if ($this->checkIfListWithCustomerData($list_type, $id_shop))
					$content .= ';;;;;;;';
			}
			$content .= ';U'."\n";
			$rq_sql_delete .= '(\''.pSql($r['email']).'\'),';
			if ($i == 1000)
			{
				$this->deletedNewUnsubscribers($rq_sql_delete, $list_type, $id_shop, $id_sd_id);
				$rq_sql_delete = '';
				$i = 0;
			}
			$i++;
		}
		if ($type_action == 'is_updated')
			return 0;
		if ($rq_sql_delete != '')
			$this->deletedNewUnsubscribers($rq_sql_delete, $list_type, $id_shop, $id_sd_id);
		return $content;
	}

	private function deletedNewUnsubscribers($rq_sql_delete, $list_type, $id_shop, $id_sd_id)
	{
		$rq_sql_delete = Tools::substr($rq_sql_delete, 0, -1);
		$rq_sql = '
		DELETE FROM `'._DB_PREFIX_.'sarbacanedesktop`
		WHERE (`email`)
		IN ('.$rq_sql_delete.')
		AND `list_type` = \''.pSql($list_type).'\'
		AND `id_shop` = \''.pSql($id_shop).'\'
		AND `id_sd_id` = \''.pSql($id_sd_id).'\'';
		Db::getInstance()->execute($rq_sql);
	}

	private function getConfiguration($return = 'nb_configured')
	{
		$sd_token = Configuration::getGlobalValue('SARBACANEDESKTOP_TOKEN');
		$sd_list = Configuration::getGlobalValue('SARBACANEDESKTOP_LIST');
		$sd_is_user = Configuration::getGlobalValue('SARBACANEDESKTOP_IS_USER');
		if ($return == 'sd_token' || $return == 'sd_list' || $return == 'sd_is_user')
		{
			if ($return == 'sd_token')
				return $sd_token;
			if ($return == 'sd_list')
				return $sd_list;
			if ($return == 'sd_is_user')
				return $sd_is_user;
		}
		else
		{
			if ($return == 'all')
				return array(
					'sd_token' => $sd_token,
					'sd_list' => $sd_list,
					'sd_is_user' => $sd_is_user
				);
			else
			{
				$nb_configured = 0;
				if ($sd_token != '')
					$nb_configured = 3;
				else
				{
					if ($sd_list != '')
						$nb_configured++;
					if ($sd_is_user != '')
						$nb_configured++;
				}
				return $nb_configured;
			}
		}
	}

	private function getSdidIdentifier($sdid)
	{
		$rq_sql = '
		SELECT `id_sd_id`
		FROM `'._DB_PREFIX_.'sarbacanedesktop_users`
		WHERE `sd_id` = \''.pSql($sdid).'\'
		ORDER BY `id_sd_id` ASC
		LIMIT 0, 1';
		$rq = Db::getInstance()->executeS($rq_sql);
		if (is_array($rq))
		{
			foreach ($rq as $r)
				return $r['id_sd_id'];
		}
		return '';
	}

	private function saveSdid($sdid)
	{
		$rq_sql = '
		INSERT INTO `'._DB_PREFIX_.'sarbacanedesktop_users` (`sd_id`) VALUES
		(\''.pSql($sdid).'\')';
		Db::getInstance()->execute($rq_sql);
		return $this->getSdidIdentifier($sdid);
	}

	private function saveTokenParameterConfiguration()
	{
		$rq_sql = 'TRUNCATE `'._DB_PREFIX_.'sarbacanedesktop`';
		Db::getInstance()->execute($rq_sql);
		$rq_sql = 'TRUNCATE `'._DB_PREFIX_.'sarbacanedesktop_users`';
		Db::getInstance()->execute($rq_sql);
		$token_parameter = rand(100000, 999999).time();
		Configuration::updateGlobalValue('SARBACANEDESKTOP_TOKEN', $token_parameter);
		Db::getInstance()->execute($rq_sql);
	}

	private function saveSdIsUser()
	{
		if (Tools::getIsset('sd_is_user'))
		{
			$sd_is_user = Tools::getValue('sd_is_user');
			Configuration::updateGlobalValue('SARBACANEDESKTOP_IS_USER', $sd_is_user);
		}
	}

	private function deleteSdUser($id_sd_id)
	{
		$rq_sql = '
		DELETE FROM `'._DB_PREFIX_.'sarbacanedesktop`
		WHERE `id_sd_id` = \''.pSql($id_sd_id).'\'';
		Db::getInstance()->execute($rq_sql);
		$rq_sql = '
		DELETE FROM `'._DB_PREFIX_.'sarbacanedesktop_users`
		WHERE `id_sd_id` = \''.pSql($id_sd_id).'\'';
		Db::getInstance()->execute($rq_sql);
	}

	private function resetList($list_type, $id_shop, $id_sd_id = '')
	{
		$rq_sql = '
		DELETE FROM `'._DB_PREFIX_.'sarbacanedesktop`
		WHERE `list_type` = \''.pSql($list_type).'\'
		AND `id_shop` = \''.pSql($id_shop).'\'';
		if ($id_sd_id != '')
			$rq_sql .= '
			AND `id_sd_id` = \''.pSql($id_sd_id).'\'';
		Db::getInstance()->execute($rq_sql);
	}

	private function getListConfiguration($return = 'string')
	{
		$sd_list = Configuration::getGlobalValue('SARBACANEDESKTOP_LIST');
		if ($return == 'string')
			return $sd_list;
		else
		{
			if (Tools::strlen($sd_list) != 0)
				return explode(',', $sd_list);
			return array();
		}
	}

	private function getKeyForSynchronisation()
	{
		return str_rot13('modules/sarbacanedesktop/sd.php?stk='.$this->getToken());
	}

	private function saveListConfiguration()
	{
		$shops = '';
		if (Tools::getIsset('id_shop'))
		{
			$id_shops = Tools::getValue('id_shop');
			if (is_array($id_shops))
				$shops = implode(',', $id_shops);
		}
		$old_sd_list_array = $this->getListConfiguration('array');
		Configuration::updateGlobalValue('SARBACANEDESKTOP_LIST', $shops);
		$sd_list_array = $this->getListConfiguration('array');
		foreach ($sd_list_array as $sd_list)
		{
			if (!in_array($sd_list, $old_sd_list_array))
			{
				$id_shop = $this->getStoreidFromList($sd_list);
				$list_type = $this->getListTypeFromList($sd_list);
				$this->resetList($list_type, $id_shop);
			}
		}
	}

	private function getSdFormKey()
	{
		return Tools::substr(Tools::encrypt('SarbacaneDesktopForm'), 0, 15);
	}

	public function getContent()
	{
		$general_configuration = $this->getConfiguration('nb_configured');
		$displayed_step = 1;
		if ($general_configuration == 1)
			$displayed_step = 2;
		else if ($general_configuration == 2 || $general_configuration == 3)
			$displayed_step = 3;
		if (Tools::isSubmit('submit_is_user') || Tools::isSubmit('submit_configuration') || Tools::isSubmit('submit_parameter_key'))
		{
			if (Tools::getIsset('sd_form_key'))
			{
				if (Tools::getValue('sd_form_key') == $this->getSdFormKey())
				{
					if (Tools::isSubmit('submit_is_user'))
					{
						$this->saveSdIsUser();
						$general_configuration = $this->getConfiguration('nb_configured');
						$displayed_step = 2;
					}
					else if (Tools::isSubmit('submit_configuration'))
					{
						$this->saveListConfiguration();
						if (Configuration::getGlobalValue('SARBACANEDESKTOP_TOKEN') == '')
							$this->saveTokenParameterConfiguration();
						$general_configuration = $this->getConfiguration('nb_configured');
						$displayed_step = 3;
					}
					else if (Tools::isSubmit('submit_parameter_key'))
						$this->saveTokenParameterConfiguration();
				}
			}
		}
		$sd_submit_url = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller')).'&token='.Tools::safeOutput(Tools::getValue('token'));
		$sd_submit_url .= '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'#sd_step';
		$this->context->smarty->assign(array(
			'sd_submit_url' => $sd_submit_url,
			'sd_form_key' => $this->getSdFormKey(),
			'key_for_synchronisation' => $this->getKeyForSynchronisation(),
			'list_configuration' => $this->getListConfiguration('array'),
			'general_configuration' => $general_configuration,
			'sd_is_user' => Configuration::getGlobalValue('SARBACANEDESKTOP_IS_USER'),
			'displayed_step' => $displayed_step,
			'stores_array' => $this->getStoresArray(),
			'website_url' => Tools::getHttpHost(true).__PS_BASE_URI__,
			'css_url' => $this->_path.'views/css/sarbacanedesktop.css',
			'js_url' => $this->_path.'views/js/sarbacanedesktop.js'
		));
		return $this->context->smarty->fetch($this->local_path.'views/templates/admin/sarbacanedesktop.tpl');
	}

}
