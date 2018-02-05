<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class Ps_KortaValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$this->context->cookie->card_no ="";
		$this->context->cookie->pay_date ="";
		$this->context->cookie->cardtype = "";

		if(!isset($_REQUEST['reference']) ||  !isset($_REQUEST['card4'])){
			Tools::redirect('index.php?controller=cart&action=show');
		}
		$this->context->cookie->card_no = $_REQUEST['card4'];
		$this->context->cookie->pay_date = $_REQUEST['time'];
		$this->context->cookie->cardtype = $_REQUEST['cardbrand'];
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');
		$customer = new Customer($cart->id_customer);
		$cart_id = Tools::getvalue('cart_id');

		$order_status = (int)Configuration::get('PS_OS_PAYMENT');

		$order_total = $cart->getOrderTotal(true, Cart::BOTH);

		$this->module->validateOrder($cart->id, $order_status, $order_total, "Korta Payment", null, array(), null, false, $cart->secure_key);


		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	}
}
