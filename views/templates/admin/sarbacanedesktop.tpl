{**
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
*}

<link href="{$css_url|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="{$js_url|escape:'htmlall':'UTF-8'}"></script>
<div id="sarbacanedesktop">
	<div class="sd_header">
		<div class="sd_logo_{l s='sarbacane' mod='sarbacanedesktop'}"></div>
	</div>
	<p class="sd_title">{l s='It\'s easy to manage your newsletter and email campaigns' mod='sarbacanedesktop'}</p>
	<div class="sd_title_separator_page"></div>
	<div class="sd_video_config_container">
		<div class="sd_video_container">
			<iframe width="565" height="315" src="{l s='https://www.youtube.com/embed/eLMy2tSSYgE' mod='sarbacanedesktop'}?rel=0&showinfo=0" frameborder="0" allowfullscreen></iframe>
		</div>
		<div class="sd_config_container">
			<p>{l s='This Prestashop module enables you to synchronize clients and accounts that have subscribed to your newsletter from your shop online using Sarbacane Desktop\'s email marketing software.' mod='sarbacanedesktop'}</p>
			<p><input type="button" onclick="sdDisplayStep(1)" class="sd_config_button" value="{l s='Begin the set-up' mod='sarbacanedesktop'}"/></p>
		</div>
	</div>
	<div class="sd_separator"></div>
	<div class="sd_detail_description_container">
		<div>
			<div>
				<div>
					<div class="sd_picto1"></div>
					<p class="sd_long_subtitle">{l s='Synchronization of your shop data' mod='sarbacanedesktop'}</p>
				</div>
			</div>
			<div>
				<div>
					<div class="sd_picto2"></div>
					<p class="sd_long_subtitle">{l s='Responsive visual editor' mod='sarbacanedesktop'}</p>
				</div>
			</div>
			<div>
				<div>
					<div class="sd_picto3"></div>
					<p class="sd_short_subtitle">{l s='Detailed statistics' mod='sarbacanedesktop'}</p>
				</div>
			</div>
			<div>
				<div>
					<div class="sd_picto4"></div>
					<p class="sd_short_subtitle">{l s='Optimal deliverability' mod='sarbacanedesktop'}</p>
				</div>
			</div>
		</div>
		<div>
			<div>
				<div>
					<div class="sd_subtitle_separator"></div>
					<p>{l s='Synchronize and manage all the email lists from your Prestashop store' mod='sarbacanedesktop'}</p>
				</div>
			</div>
			<div>
				<div>
					<div class="sd_subtitle_separator"></div>
					<p>{l s='NEW! Create awesome, responsive newsletters thanks to the EmailBuilder' mod='sarbacanedesktop'}</p>
				</div>
			</div>
			<div>
				<div>
					<div class="sd_subtitle_separator"></div>
					<p>{l s='Geolocation, openings, opening time, clicks, opt-outs...' mod='sarbacanedesktop'}</p>
				</div>
			</div>
			<div>
				<div>
					<div class="sd_subtitle_separator"></div>
					<p>{l s='Optimal deliverability thanks to our renowned professional routing platform' mod='sarbacanedesktop'}</p>
				</div>
			</div>
		</div>
	</div>
	<div class="sd_separator"></div>
	<div class="sd_account_offer_container">
		<p class="sd_account">{l s='Create your account for free and start sending emails' mod='sarbacanedesktop'}</p>
		<p class="sd_offer">{l s='No strings attached' mod='sarbacanedesktop'}</p>
		<div class="sd_title_separator_account_offer"></div>
	</div>
	<div class="sd_info_container">
		<div class="sd_info_container_left">
			<div>
				<p class="sd_info_title">{l s='Why choose Sarbacane?' mod='sarbacanedesktop'}</p>
				<div>
					<p>{l s='Create your account for free and start sending emails' mod='sarbacanedesktop'}</p>
					<p>{l s='Trusted by over 20,000 users worldwide.' mod='sarbacanedesktop'}</p>
					<p>{l s='Awarded Best Emailing Solution by Bsoco Awards (an index that compares emailing solutions)' mod='sarbacanedesktop'}</p>
					<p>{l s='All you need to succeed: design, customize, send, and follow-up on your campaigns' mod='sarbacanedesktop'}</p>
					<p>{l s='A tech heldpdesk and a variety of resources to help: videos, tutorials, manuals, tips...' mod='sarbacanedesktop'}</p>
				</div>
			</div>
		</div>
		<div class="sd_info_container_right">
			<div>
				<p class="sd_info_title">{l s='Need help?' mod='sarbacanedesktop'}</p>
				<div>
					<p>{l s='Email:' mod='sarbacanedesktop'} {l s='support@sarbacane.com' mod='sarbacanedesktop'}</p>
					<p>{l s='Tel:' mod='sarbacanedesktop'} {l s='+33(0) 328 328 040' mod='sarbacanedesktop'}</p>
					<p>
						{l s='Website:' mod='sarbacanedesktop'}
						<a href="{l s='http://www.sarbacane.com/?utm_source=module-prestashop&utm_medium=plugin&utm_content=lien-sarbacane&utm_campaign=prestashop' mod='sarbacanedesktop'}" target="_blank">{l s='http://www.sarbacane.com' mod='sarbacanedesktop'}</a>
					</p>
				</div>
			</div>
		</div>
	</div>
	<div id="sd_step"></div>
	<div id="sd_step1" class="sd_step{if $displayed_step == 1} sd_show_step{/if}">
		<p class="sd_step_title">{l s='How to set up the module?' mod='sarbacanedesktop'}</p>
		<div class="sd_step_line"></div>
		<p class="sd_step1_instruction">{l s='Do you already have a Sarbacane Desktop account?' mod='sarbacanedesktop'}</p>
		<form autocomplete="off" action="{$sd_submit_url|escape:'htmlall':'UTF-8'}" method="post">
			<p class="sd_step1_selection">
				<span>
					<input onclick="sdUserYesNoDisplayButton('yes')" type="radio" id="sd_user_click_yes" name="sd_is_user" value="yes"{if $sd_is_user == 'yes'} checked="checked"{/if}/>
					<label for="sd_user_click_yes">{l s='Yes' mod='sarbacanedesktop'}</label>
				</span>
				<span>
					<input onclick="sdUserYesNoDisplayButton('no')" type="radio" id="sd_user_click_no" name="sd_is_user" value="no"{if $sd_is_user == 'no'} checked="checked"{/if}/>
					<label for="sd_user_click_no">{l s='No' mod='sarbacanedesktop'}</label>
				</span>
			</p>
			<div class="sd_step_buttons">
				<div class="sd_step_buttons_right">
					<input type="hidden" name="submit_is_user" value="1"/>
					<input type="hidden" name="sd_form_key" value="{$sd_form_key|escape:'htmlall':'UTF-8'}"/>
					<input type="submit" class="sd_button{if $sd_is_user == ''} sd_step1_button{/if}" value="{l s='Next' mod='sarbacanedesktop'}"/>
				</div>
			</div>
		</form>
	</div>
	<div id="sd_step2" class="sd_step{if $displayed_step == 2} sd_show_step{/if}">
		<p class="sd_step_title">{l s='How to set up the module?' mod='sarbacanedesktop'}</p>
		<div class="sd_step_line"></div>
		<p class="sd_step2_instruction">{l s='Select your shop and pick your settings' mod='sarbacanedesktop'}</p>
		<form autocomplete="off" action="{$sd_submit_url|escape:'htmlall':'UTF-8'}" method="post">
			<div class="sd_step2_selection">
				{foreach $stores_array as $store}
				<div class="sd_step2_selection_list">
					<p class="sd_step2_selection_shop_title">{$store['name']|escape:'htmlall':'UTF-8'}</p>
					<div class="sd_step2_selection_shop">
						{if $general_configuration < 3 || in_array($store['id_shop']|cat:'N0', $list_configuration)}
							{assign var='checked_boolean_newsletter' value=true}
						{else}
							{assign var='checked_boolean_newsletter' value=false}
						{/if}
						<div class="sd_step2_selection_shop_input">
							<input type="checkbox" name="id_shop[]" {if $checked_boolean_newsletter == true}checked="checked" {/if}value="{$store['id_shop']|escape:'htmlall':'UTF-8'}N0"/>&nbsp;
						</div>
						<div class="sd_step2_selection_shop_text">
							{l s='Create a list in Sarbacane Desktop with newsletter opt-ins' mod='sarbacanedesktop'}
						</div>
					</div>
					<div class="sd_step2_selection_shop">
						{if $general_configuration < 3 || in_array($store['id_shop']|cat:'C0', $list_configuration) || in_array($store['id_shop']|cat:'C1', $list_configuration)}
							{assign var='checked_boolean_customer' value=true}
						{else}
							{assign var='checked_boolean_customer' value=false}
						{/if}
						{if in_array($store['id_shop']|cat:'C1', $list_configuration)}
							{assign var='checked_value_customer' value=$store['id_shop']|cat:'C1'}
						{else}
							{assign var='checked_value_customer' value=$store['id_shop']|cat:'C0'}
						{/if}
						<div class="sd_step2_selection_shop_input">
							<input id="id_shop_{$store['id_shop']|escape:'htmlall':'UTF-8'}C" type="checkbox" name="id_shop[]" onclick="changeOptionOrdersDataDisplay('{$store['id_shop']|escape:'htmlall':'UTF-8'}C', this.checked)" {if $checked_boolean_customer == true}checked="checked" {/if}value="{$checked_value_customer|escape:'htmlall':'UTF-8'}"/>&nbsp;
						</div>
						<div class="sd_step2_selection_shop_text">
							{l s='Create a list in Sarbacane Desktop with your clients who have an account or who have placed an order online' mod='sarbacanedesktop'}
						</div>
					</div>
					<div class="sd_step2_selection_shop_option">
						{if in_array($store['id_shop']|cat:'C1', $list_configuration)}
							{assign var='checked_value_customer_data' value=$store['id_shop']|cat:'C1'}
						{else}
							{assign var='checked_value_customer_data' value=$store['id_shop']|cat:'C0'}
						{/if}
						{if in_array($store['id_shop']|cat:'C1', $list_configuration)}
							{assign var='checked_boolean_customer_data' value=true}
						{else}
							{assign var='checked_boolean_customer_data' value=false}
						{/if}
						<div class="sd_step2_selection_shop_input">
							<input id="sd_customer_data_{$store['id_shop']|escape:'htmlall':'UTF-8'}C" type="checkbox" {if $checked_boolean_customer == false}disabled="disabled" {/if}onclick="changeOptionOrdersData('{$store['id_shop']|escape:'htmlall':'UTF-8'}C', this.checked)" {if $checked_boolean_customer_data == true}checked="checked" {/if}value=""/>&nbsp;
						</div>
						<div class="sd_step2_selection_shop_text">
							{l s='Add order data (date, amount, etc...)' mod='sarbacanedesktop'}
						</div>
						<div onclick="sdInfoDataOrdersClick('{$store['id_shop']|escape:'htmlall':'UTF-8'}C')" onmouseover="sdInfoDataOrdersHover('{$store['id_shop']|escape:'htmlall':'UTF-8'}C')" onmouseout="sdInfoDataOrdersOut('{$store['id_shop']|escape:'htmlall':'UTF-8'}C')" class="sd_step2_info"></div>
					</div>
				</div>
				<div id="sd_tooltip_{$store['id_shop']|escape:'htmlall':'UTF-8'}C" class="sd_tooltip">
					<div>
						{l s='As you enable data from orders, you can also gather extra info in your contact list, and target your recipients in Sarbacane Desktop.' mod='sarbacanedesktop'}
						<br/>{l s='Below are pieces of information that will be added to your contact list:' mod='sarbacanedesktop'}
						<br/>{l s='- Date of first order' mod='sarbacanedesktop'}
						<br/>{l s='- Date of latest order' mod='sarbacanedesktop'}
						<br/>{l s='- Total number of orders' mod='sarbacanedesktop'}
						<br/>{l s='- Total amount of orders' mod='sarbacanedesktop'}
						<br/>{l s='- Amount of the cheapest order' mod='sarbacanedesktop'}
						<br/>{l s='- Amount of the most expensive order' mod='sarbacanedesktop'}
						<br/>{l s='- Average amount of the orders placed' mod='sarbacanedesktop'}
					</div>
				</div>
				{/foreach}
			</div>
			<div class="sd_step_buttons">
				<div class="sd_step_buttons_left">
					<input type="button" onclick="sdDisplayStep(1)" value="{l s='Previous' mod='sarbacanedesktop'}"/>
				</div>
				<div class="sd_step_buttons_right">
					<input type="hidden" name="submit_configuration" value="1"/>
					<input type="hidden" name="sd_form_key" value="{$sd_form_key|escape:'htmlall':'UTF-8'}"/>
					<input type="submit" class="sd_button" value="{l s='Next' mod='sarbacanedesktop'}"/>
				</div>
			</div>
		</form>
	</div>
	<div id="sd_step3" class="sd_step{if $displayed_step == 3} sd_show_step{/if}">
		<p class="sd_step_title">{l s='How to set up the module?' mod='sarbacanedesktop'}</p>
		<div class="sd_step_line"></div>
		<div class="sd_step3_instruction">
			{if $sd_is_user == 'no'}
			<p>1/ <a href="{l s='https://www.sarbacane.com/ws/soft-redirect.asp?key=heXmrxBEUO&com=PrestaShopInfo' mod='sarbacanedesktop'}" target="_blank">{l s='Download and install Sarbacane' mod='sarbacanedesktop'}</a></p>
			<p>2/ {l s='Create your free account' mod='sarbacanedesktop'}</p>
			<p>3/ {l s='Enable the Prestashop extension in our extensions menu, and then fill in the following fields:' mod='sarbacanedesktop'}</p>
			{else if $sd_is_user == 'yes'}
			<p>{l s='Enable the Prestashop extension in our extensions menu, and then fill in the following fields:' mod='sarbacanedesktop'}</p>
			{/if}
			<form autocomplete="off" action="{$sd_submit_url|escape:'htmlall':'UTF-8'}" method="post">
				<div class="sd_key_container">
					<p>{l s='Url' mod='sarbacanedesktop'}</p>
					<input value="{$website_url|escape:'htmlall':'UTF-8'}" onclick="this.select()" type="text" readonly/>
				</div>
				<div class="sd_key_container">
					<p>{l s='Key' mod='sarbacanedesktop'}</p>
					<input value="{$key_for_synchronisation|escape:'htmlall':'UTF-8'}" onclick="this.select()" type="text" readonly/>
				</div>
				<p class="sd_key_button_container">
					<input type="hidden" name="submit_parameter_key" value="1"/>
					<input type="hidden" name="sd_form_key" value="{$sd_form_key|escape:'htmlall':'UTF-8'}"/>
					<input type="submit" class="sd_key_button" value="{l s='Generate a new key' mod='sarbacanedesktop'}"/>
				</p>
			</form>
		</div>
		<div class="sd_step_buttons">
			<div class="sd_step_buttons_left">
				<input type="button" onclick="sdDisplayStep(2)" value="{l s='Previous' mod='sarbacanedesktop'}"/>
			</div>
			<div class="sd_step3_right">
				{l s='Read more' mod='sarbacanedesktop'}, <a href="{l s='http://www.sarbacane.com/faq/extensions/configuration-plugin-prestashop/?utm_source=module-prestashop&utm_medium=plugin&utm_content=lien-sarbacane&utm_campaign=prestashop' mod='sarbacanedesktop'}" target="_blank">{l s='in the help section online' mod='sarbacanedesktop'}</a>
			</div>
		</div>
	</div>
</div>
