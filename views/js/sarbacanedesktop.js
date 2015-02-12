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

function changeOptionOrdersData(id_shop_c, value) {
	if(value == true) {
		$('#id_shop_' + id_shop_c).val(id_shop_c + '1');
	}
	else {
		$('#id_shop_' + id_shop_c).val(id_shop_c + '0');
	}
}

function changeOptionOrdersDataDisplay(id_shop_c, value) {
	if(value == true) {
		$('#sd_customer_data_' + id_shop_c).removeAttr('disabled');
	}
	else {
		$('#sd_customer_data_' + id_shop_c).attr('disabled', 'disabled');
		$('#sd_customer_data_' + id_shop_c).removeAttr('checked');
	}
}

function sdUserYesNoDisplayButton(user_selection) {
	$('#sd_step1 .sd_button').removeClass('sd_step1_button');
}

function sdDisplayStep(step) {
	$('.sd_step').removeClass('sd_show_step');
	$('#sd_step' + step).addClass('sd_show_step');
	window.location.href = '#sd_step';
}

function sdInfoDataOrdersHover(id_shop_c) {
	$('#sd_tooltip_' + id_shop_c).addClass('sd_tooltip_show');
}

function sdInfoDataOrdersOut(id_shop_c) {
	$('#sd_tooltip_' + id_shop_c).removeClass('sd_tooltip_show');
}

function sdInfoDataOrdersClick(id_shop_c) {
	$('#sd_tooltip_' + id_shop_c).toggleClass('sd_tooltip_show');
}