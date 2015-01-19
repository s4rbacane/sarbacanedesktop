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
		$this->version = '1.0';
		$this->name = 'sarbacanedesktop';
		$this->tab = 'emailing';
		$this->author = 'Sarbacane Software';
		$this->need_instance = 0;
		$this->bootstrap = true;
		parent::__construct();
		$this->displayName = $this->l('Contact lists - Sarbacane Desktop');
		$this->description = $this->l('Synchronize contact lists with data from PrestaShop.');
		$this->ps_versions_compliancy = array('min' => '1.5.0.9', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		if (!$this->checkPrestashopVersion())
			return false;
		$result1 = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop`');
		$result2 = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop_users`');
		$result3  = Db::getInstance()->execute('
		CREATE TABLE `'._DB_PREFIX_.'sarbacanedesktop` (
			`email` varchar(150) NOT NULL,
			`list_type` varchar(20) NOT NULL,
			`id_shop` varchar(20) NOT NULL,
			`id_sd` varchar(20) NOT NULL,
			`customer_data` varchar(100) NOT NULL,
			`orders_data` varchar(150) NOT NULL,
			PRIMARY KEY(`email`, `list_type`, `id_shop`, `id_sd`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
		$result4  = Db::getInstance()->execute('
		CREATE TABLE `'._DB_PREFIX_.'sarbacanedesktop_users` (
			`id_sd` int(20) unsigned NOT NULL AUTO_INCREMENT,
			`sd_type` varchar(20) NOT NULL,
			`sd_value` varchar(200) NOT NULL,
			PRIMARY KEY(`id_sd`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
		$result5 = Db::getInstance()->execute('
		INSERT INTO `'._DB_PREFIX_.'sarbacanedesktop_users` (`sd_type`, `sd_value`) VALUES
		(\'sd_token\', \'\'),
		(\'sd_list\', \'\'),
		(\'sd_is_user\', \'\')');
		if (!$result1 || !$result2 || !$result3 || !$result4 || !$result5)
			return false;
		if (!parent::install())
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop`');
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'sarbacanedesktop_users`');
		return true;
	}

	public function initSynchronisation()
	{
		if (Module::isInstalled($this->name) && Tools::getIsset('stk') && Tools::getIsset('sdid') && $this->checkPrestashopVersion())
		{
			if (Tools::getValue('stk') == $this->getToken())
			{
				$sdid = Tools::getvalue('sdid');
				if ($sdid != '' && $this->getConfiguration('nb_configured') == 3)
				{
					$id_sd = $this->getSdidIdentifier($sdid);
					if ($id_sd == '' && !Tools::getIsset('list'))
						$id_sd = $this->saveSdid($sdid);
					if ($id_sd != '')
					{
						$configuration = $this->getConfiguration('all');
						if ($configuration['sd_token'] != '' && $configuration['sd_list'] != '')
						{
							$sd_list_array = $this->getListConfiguration('array');
							if (is_array($sd_list_array))
							{
								if (count($sd_list_array) > 0)
								{
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
												if (Tools::getIsset('action'))
												{
													if (Tools::getValue('action') == 'reset')
														$this->resetList($list_type, $id_shop, $id_sd);
												}
												$this->processNewUnsubcribersAndSubscribers($list_type, $id_shop, $id_sd);
											}
										}
									}
									else
										$this->getFormattedContentShops($id_sd);
								}
							}
						}
					}
				}
			}
		}
	}

	private function checkPrestashopVersion()
	{
		if (version_compare(_PS_VERSION_, '1.5.0.9', '<'))
			return false;
		return true;
	}

	private function getToken()
	{
		$str = $this->getConfiguration('sd_token');
		$str = $str.Tools::substr(Tools::encrypt('SecurityTokenForModule'), 0, 11).$str;
		$str = Tools::encrypt($str);
		return $str;
	}

	private function processNewUnsubcribersAndSubscribers($list_type, $id_shop, $id_sd)
	{
		$line = 'email;lastname;firstname';
		if ($list_type == 'C')
		{
			$line .= ';partners';
			if ($this->checkIfListWithCustomerData($list_type, $id_shop))
				$line .= ';date_first_order;date_last_order;amount_min_order;amount_max_order;amount_avg_order;nb_orders;amount_all_orders';
		}
		$line .= ';action';
		$line .= "\n";
		echo $line;
		$this->processNewUnsubscribers($list_type, $id_shop, $id_sd);
		$this->processNewSubscribers($list_type, $id_shop, $id_sd);
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

	private function getFormattedContentShops($id_sd)
	{
		$stores = $this->getStoresArray();
		echo 'list_id;name;reset;is_updated;type;version'."\n";
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
					$store_list .= $this->listIsResetted($store['id_shop'], $list['list_type'], $id_sd).';';
					$store_list .= $this->listIsUpdated($store['id_shop'], $list['list_type'], $id_sd).';';
					$store_list .= 'Prestashop;'.$this->version."\n";
					echo $store_list;
				}
			}
		}
	}

	private function listIsResetted($id_shop, $list_type, $id_sd)
	{
		$rq_sql = '
		SELECT count(`email`) AS `nb_in_table`
		FROM `'._DB_PREFIX_.'sarbacanedesktop`
		WHERE `list_type` = \''.pSql($list_type).'\'
		AND `id_shop` = \''.pSql($id_shop).'\'
		AND `id_sd` = \''.pSql($id_sd).'\'';
		$nb_in_table = Db::getInstance()->getValue($rq_sql);
		if ($nb_in_table == 0)
			return 'Y';
		return 'N';
	}

	private function listIsUpdated($id_shop, $list_type, $id_sd)
	{
		$is_updated_list = 'N';
		if ($this->processNewUnsubscribers($list_type, $id_shop, $id_sd, 'is_updated') > 0)
			$is_updated_list = 'Y';
		if ($this->processNewSubscribers($list_type, $id_shop, $id_sd, 'is_updated') > 0)
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

	private function checkIfNewsletterModule()
	{
		$rq_sql = '
		SELECT count(*)
		FROM `'._DB_PREFIX_.'module`
		WHERE name = \'blocknewsletter\'
		AND `active` = 1';
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
			$rq_sql = '
			SELECT gs.`id_group_shop`
			FROM `'._DB_PREFIX_.'shop` s,
			`'._DB_PREFIX_.'group_shop` gs
			WHERE s.`id_group_shop` = gs.`id_group_shop`
			AND s.`id_shop` = '.(int)$id_shop.'
			AND gs.`share_customer` = 1';
		else
			$rq_sql = '
			SELECT sg.`id_shop_group`
			FROM `'._DB_PREFIX_.'shop` s,
			`'._DB_PREFIX_.'shop_group` sg
			WHERE s.`id_shop_group` = sg.`id_shop_group`
			AND s.`id_shop` = '.(int)$id_shop.'
			AND sg.`share_customer` = 1';
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

	private function processNewSubscribers($list_type, $id_shop, $id_sd, $type_action = 'display')
	{
		$check_if_newsletter_module = $this->checkIfNewsletterModule();
		$shop_customer_selection = $this->getShopCustomerSelection($id_shop);
		$rq_sql_limit = '2500';
		if ($type_action == 'is_updated')
			$rq_sql_limit = '1';
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
						FROM `'._DB_PREFIX_.'newsletter` n,
						`'._DB_PREFIX_.'module` m,
						`'._DB_PREFIX_.'module_shop` ms
						WHERE m.`name` = \'blocknewsletter\'
						AND m.`active` = 1
						AND m.`id_module` = ms.`id_module`
						AND ms.`id_shop` = n.`id_shop`
						AND n.`id_shop` = '.(int)$id_shop.'
						AND n.`active` = 1';
						$rq_sql .= '
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
			LEFT JOIN `'._DB_PREFIX_.'sarbacanedesktop` s ON (
				s.`email` = t.`email` AND s.`list_type` = \'N\' AND s.`id_shop` = \''.pSql($id_shop).'\' AND s.`id_sd` = \''.pSql($id_sd).'\'
				AND s.`customer_data` = CONCAT(t.`lastname`, \'_\', t.`firstname`)
			)
			WHERE s.`id_shop` IS NULL
			LIMIT 0, '.$rq_sql_limit;
		}
		else if ($list_type == 'C')
		{
			$add_customer_data = $this->checkIfListWithCustomerData($list_type, $id_shop);
			$rq_sql = '
			SELECT t.* FROM (
				SELECT c.`email`, c.`lastname`, c.`firstname`, c.`optin`';
				if ($add_customer_data)
					$rq_sql .= ',
					IFNULL(MIN(o.`date_add`), \'\') AS `date_first_order`, IFNULL(MAX(o.`date_add`), \'\') AS `date_last_order`,
					IFNULL(MIN(o.`total_paid_tax_incl`), \'\') AS `amount_min_order`, IFNULL(MAX(o.`total_paid_tax_incl`), \'\') AS `amount_max_order`,
					IFNULL(ROUND(AVG(o.`total_paid_tax_incl`), 6), \'\') AS `amount_avg_order`,
					IFNULL(SUM(o.`one_order`), \'\') AS `nb_orders`, IFNULL(SUM(o.`total_paid_tax_incl`), \'\') AS `amount_all_orders`';
				$rq_sql .= '
				FROM `'._DB_PREFIX_.'customer` c';
				if ($add_customer_data)
					$rq_sql .= '
					LEFT JOIN (
						SELECT o.`total_paid_tax_incl`, o.`date_add`, cus.`email`, 1 AS `one_order`
						FROM `'._DB_PREFIX_.'orders` o,
						`'._DB_PREFIX_.'customer` cus
						WHERE o.`id_shop` = '.(int)$id_shop.'
						AND o.`id_customer` = cus.`id_customer`
					) AS o ON o.`email` = c.`email`';
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
			LEFT JOIN `'._DB_PREFIX_.'sarbacanedesktop` s ON (
				s.`email` = t.`email` AND s.`list_type` = \'C\' AND s.`id_shop` = \''.pSql($id_shop).'\' AND s.`id_sd` = \''.pSql($id_sd).'\'
				AND s.`customer_data` = CONCAT(t.`lastname`, \'_\', t.`firstname`, t.`optin`)';
				if ($add_customer_data)
					$rq_sql .= '
					AND s.`orders_data` = CONCAT(t.`amount_min_order`, t.`amount_max_order`, t.`nb_orders`, t.`amount_all_orders`)';
			$rq_sql .= '
			)
			WHERE s.`id_shop` IS NULL
			LIMIT 0, '.$rq_sql_limit;
		}
		else
			return array();
		$rq = Db::getInstance()->executeS($rq_sql);
		$rq_sql_insert = '';
		if (is_array($rq))
		{
			$nb_results = count($rq);
			if ($type_action == 'is_updated')
				return $nb_results;
			$i = 0;
			foreach ($rq as $key => $r)
			{
				$line = $this->dQuote($r['email']).';';
				$line .= $this->dQuote($r['lastname']).';'.$this->dQuote($r['firstname']);
				if ($list_type == 'C')
				{
					$line .= ';'.$r['optin'];
					if ($add_customer_data)
					{
						$line .= ';'.$this->dQuote($r['date_first_order']).';'.$this->dQuote($r['date_last_order']);
						$line .= ';'.(float)$r['amount_min_order'].';'.(float)$r['amount_max_order'].';'.(float)$r['amount_avg_order'];
						$line .= ';'.(int)$r['nb_orders'].';'.(float)$r['amount_all_orders'];
					}
				}
				$line .= ';S'."\n";
				echo $line;
				$customer_data = $r['lastname'].'_'.$r['firstname'];
				$orders_data = '';
				if ($list_type == 'C')
				{
					$customer_data .= $r['optin'];
					if ($add_customer_data)
						$orders_data = $r['amount_min_order'].$r['amount_max_order'].$r['nb_orders'].$r['amount_all_orders'];
				}
				$insert_values = '\''.pSql($r['email']).'\', \''.pSql($list_type).'\', \''.pSql($id_shop).'\', \''.pSql($id_sd).'\', \'';
				$insert_values .= pSql($customer_data).'\', \''.pSql($orders_data).'\'';
				$rq_sql_insert .= ' ('.$insert_values.'),';
				if ($key + 1 == $nb_results || $i == 200)
				{
					$rq_sql_insert = Tools::substr($rq_sql_insert, 0, -1);
					$rq_sql = '
					INSERT INTO `'._DB_PREFIX_.'sarbacanedesktop` (`email`, `list_type`, `id_shop`, `id_sd`, `customer_data`, `orders_data`) VALUES
					'.$rq_sql_insert.'
					ON DUPLICATE KEY UPDATE
					`customer_data` = VALUES(`customer_data`),
					`orders_data` = VALUES(`orders_data`)';
					Db::getInstance()->execute($rq_sql);
					$rq_sql_insert = '';
					$i = 0;
				}
				$i++;
			}
		}
	}

	private function processNewUnsubscribers($list_type, $id_shop, $id_sd, $type_action = 'display')
	{
		$check_if_newsletter_module = $this->checkIfNewsletterModule();
		$shop_customer_selection = $this->getShopCustomerSelection($id_shop);
		$rq_sql_limit = '2500';
		if ($type_action == 'is_updated')
			$rq_sql_limit = '1';
		if ($list_type == 'N')
		{
			$rq_sql = '
			SELECT s.`email`
			FROM `'._DB_PREFIX_.'sarbacanedesktop` s
			LEFT JOIN `'._DB_PREFIX_.'customer` c
				ON (c.`email` = s.`email` AND c.'.$shop_customer_selection.' AND c.`deleted` = 0 AND c.`newsletter` = 1)';
			if ($check_if_newsletter_module)
			{
				$rq_sql .= '
				LEFT JOIN (
					SELECT n.`email`, n.id_shop
					FROM `'._DB_PREFIX_.'newsletter` n,
					`'._DB_PREFIX_.'module` m,
					`'._DB_PREFIX_.'module_shop` ms
					WHERE m.`name` = \'blocknewsletter\'
					AND m.`active` = 1
					AND m.`id_module` = ms.`id_module`
					AND ms.`id_shop` = n.`id_shop`
					AND n.`id_shop` = '.(int)$id_shop.'
					AND n.`active` = 1
				) AS n ON n.`email` = s.`email`';
			}
			$rq_sql .= '
			WHERE s.`list_type` = \'N\'
			AND s.`id_shop` = \''.pSql($id_shop).'\'
			AND s.`id_sd` = \''.pSql($id_sd).'\'
			AND c.`id_shop` IS NULL';
			if ($check_if_newsletter_module)
				$rq_sql .= '
				AND n.`id_shop` IS NULL';
			$rq_sql .= '
			LIMIT 0, '.$rq_sql_limit;
		}
		else if ($list_type == 'C')
		{
			$rq_sql = '
			SELECT s.`email`
			FROM `'._DB_PREFIX_.'sarbacanedesktop` s
			LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`email` = s.`email` AND c.'.$shop_customer_selection.' AND c.`deleted` = 0)
			WHERE s.`list_type` = \'C\'
			AND s.`id_shop` = \''.pSql($id_shop).'\'
			AND s.`id_sd` = \''.pSql($id_sd).'\'
			AND c.`id_shop` IS NULL
			LIMIT 0, '.$rq_sql_limit;
		}
		else
			return;
		$rq = Db::getInstance()->executeS($rq_sql);
		$rq_sql_delete = '';
		if (is_array($rq))
		{
			$nb_results = count($rq);
			if ($type_action == 'is_updated')
				return $nb_results;
			$i = 0;
			foreach ($rq as $key => $r)
			{
				$line = $this->dQuote($r['email']).';;';
				if ($list_type == 'C')
				{
					$line .= ';';
					if ($this->checkIfListWithCustomerData($list_type, $id_shop))
						$line .= ';;;;;;;';
				}
				$line .= ';U'."\n";
				echo $line;
				$rq_sql_delete .= '(\''.pSql($r['email']).'\'),';
				if ($key + 1 == $nb_results || $i == 200)
				{
					$rq_sql_delete = Tools::substr($rq_sql_delete, 0, -1);
					$rq_sql = '
					DELETE FROM `'._DB_PREFIX_.'sarbacanedesktop`
					WHERE (`email`)
					IN ('.$rq_sql_delete.')
					AND `list_type` = \''.pSql($list_type).'\'
					AND `id_shop` = \''.pSql($id_shop).'\'
					AND `id_sd` = \''.pSql($id_sd).'\'';
					Db::getInstance()->execute($rq_sql);
					$rq_sql_delete = '';
					$i = 0;
				}
				$i++;
			}
		}
	}

	private function getConfiguration($return = 'nb_configured')
	{
		$rq_sql = '
		SELECT *
		FROM `'._DB_PREFIX_.'sarbacanedesktop_users`
		WHERE `sd_type` = \'sd_token\'
		OR `sd_type` = \'sd_list\'
		OR `sd_type` = \'sd_is_user\'';
		$rq = Db::getInstance()->executeS($rq_sql);
		$sd_token = '';
		$sd_list = '';
		$sd_is_user = '';
		if (is_array($rq))
		{
			foreach ($rq as $r)
			{
				if ($r['sd_type'] == 'sd_token')
					$sd_token = $r['sd_value'];
				else if ($r['sd_type'] == 'sd_list')
					$sd_list = $r['sd_value'];
				else if ($r['sd_type'] == 'sd_is_user')
					$sd_is_user = $r['sd_value'];
			}
		}
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
					$nb_configured++;
				if ($sd_list != '')
					$nb_configured++;
				if ($sd_is_user != '')
					$nb_configured++;
				return $nb_configured;
			}
		}
	}

	private function getSdidIdentifier($sdid)
	{
		$rq_sql = '
		SELECT `id_sd`
		FROM `'._DB_PREFIX_.'sarbacanedesktop_users`
		WHERE `sd_type` = \'id_sd\'
		AND `sd_value` = \''.pSql($sdid).'\'
		ORDER BY `id_sd` ASC
		LIMIT 0, 1';
		$rq = Db::getInstance()->executeS($rq_sql);
		if (is_array($rq))
		{
			foreach ($rq as $r)
				return $r['id_sd'];
		}
		return '';
	}

	private function saveSdid($sdid)
	{
		$rq_sql = '
		INSERT INTO `'._DB_PREFIX_.'sarbacanedesktop_users` (`sd_type`, `sd_value`) VALUES
		(\'id_sd\', \''.pSql($sdid).'\')';
		Db::getInstance()->execute($rq_sql);
		return $this->getSdidIdentifier($sdid);
	}

	private function saveTokenParameterConfiguration()
	{
		$rq_sql = 'TRUNCATE `'._DB_PREFIX_.'sarbacanedesktop`';
		Db::getInstance()->execute($rq_sql);
		$token_parameter = rand(100000, 999999).time();
		$rq_sql = '
		UPDATE `'._DB_PREFIX_.'sarbacanedesktop_users`
		SET `sd_value` = \''.pSql($token_parameter).'\'
		WHERE `sd_type` = \'sd_token\'';
		Db::getInstance()->execute($rq_sql);
	}

	private function saveSdIsUser()
	{
		if (Tools::getIsset('sd_is_user'))
		{
			$sd_is_user = Tools::getValue('sd_is_user');
			$rq_sql = '
			UPDATE `'._DB_PREFIX_.'sarbacanedesktop_users`
			SET `sd_value` = \''.pSql($sd_is_user).'\'
			WHERE `sd_type` = \'sd_is_user\'';
			Db::getInstance()->execute($rq_sql);
		}
	}

	private function resetList($list_type, $id_shop, $id_sd = '')
	{
		$rq_sql = '
		DELETE FROM `'._DB_PREFIX_.'sarbacanedesktop`
		WHERE `list_type` = \''.pSql($list_type).'\'
		AND `id_shop` = \''.pSql($id_shop).'\'';
		if ($id_sd != '')
			$rq_sql .= '
			AND `id_sd` = \''.pSql($id_sd).'\'';
		Db::getInstance()->execute($rq_sql);
	}

	private function getListConfiguration($return = 'string')
	{
		$sd_list = $this->getConfiguration('sd_list');
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
			{
				foreach ($id_shops as $id_shop)
					$shops .= $id_shop.',';
				$shops = Tools::substr($shops, 0, Tools::strlen($shops) - 1);
			}
		}
		$old_sd_list_array = $this->getListConfiguration('array');
		$rq_sql = '
		UPDATE `'._DB_PREFIX_.'sarbacanedesktop_users`
		SET `sd_value` = \''.pSql($shops).'\'
		WHERE `sd_type` = \'sd_list\'';
		Db::getInstance()->execute($rq_sql);
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
		if (!Module::isInstalled($this->name) || !$this->checkPrestashopVersion())
			return '';
		if (!isset($this->context->employee))
			return '';
		if (!$this->context->employee->isLoggedBack())
			return '';
		$general_configuration = $this->getConfiguration('nb_configured');
		if ($general_configuration == 1)
			$displayed_step = 2;
		else if ($general_configuration == 2 || $general_configuration == 3)
			$displayed_step = 3;
		else
			$displayed_step = 1;
		if (Tools::isSubmit('submit_is_user') || Tools::isSubmit('submit_configuration') || Tools::isSubmit('submit_parameter_key'))
		{
			if (Tools::getIsset('sd_form_key'))
			{
				if (Tools::getValue('sd_form_key') == $this->getSdFormKey())
				{
					if (Tools::isSubmit('submit_is_user'))
					{
						$this->saveSdIsUser();
						$displayed_step = 2;
					}
					else if (Tools::isSubmit('submit_configuration'))
					{
						$this->saveListConfiguration();
						if ($this->getConfiguration('sd_token') == '')
							$this->saveTokenParameterConfiguration();
						$displayed_step = 3;
					}
					else if (Tools::isSubmit('submit_parameter_key'))
						$this->saveTokenParameterConfiguration();
				}
			}
		}
		$logo_class = 'sd_logo';
		if ($this->context->language->iso_code == 'fr')
			$logo_class .= '_fr';
		$sd_submit_url = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller')).'&token='.Tools::safeOutput(Tools::getValue('token'));
		$sd_submit_url .= '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'#sd_step';
		$this->context->smarty->assign(array(
			'sd_submit_url' => $sd_submit_url,
			'sd_form_key' => $this->getSdFormKey(),
			'key_for_synchronisation' => $this->getKeyForSynchronisation(),
			'list_configuration' => $this->getListConfiguration('array'),
			'general_configuration' => $general_configuration,
			'sd_is_user' => $this->getConfiguration('sd_is_user'),
			'displayed_step' => $displayed_step,
			'stores_array' => $this->getStoresArray(),
			'logo_class' => $logo_class,
			'website_url' => Tools::getHttpHost(true).__PS_BASE_URI__,
			'css_url' => $this->_path.'css/sarbacanedesktop.css',
			'js_url' => $this->_path.'js/sarbacanedesktop.js'
		));
		return $this->context->smarty->fetch($this->local_path.'views/templates/admin/sarbacanedesktop.tpl');
	}

}