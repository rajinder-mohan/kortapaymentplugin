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
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
if (!defined('_PS_VERSION_')) {
    exit;
}


require_once _PS_MODULE_DIR_.'ps_korta/classes/Korta.php';

class Ps_Korta extends PaymentModule
{
    private $templateFile;
     public $html = '';

    public function __construct()
    {
        $this->name = 'ps_korta';
        $this->author = 'PrestaShop';
        $this->version = '1.0.7';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Korta Payments', array(), 'Modules.Korta');
        $this->description = $this->trans('Configure Korta Payment', array(), 'Modules.Korta');

        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:ps_korta/ps_korta.tpl';

    }

    public function install()
    {



      if (Shop::isFeatureActive())
          Shop::setContext(Shop::CONTEXT_ALL);


      return  parent::install() &&
          $this->installDB() &&
          $this->registerHook('paymentReturn') &&
          $this->registerHook('paymentOptions');

    }

    public function uninstall()
    {
        // return parent::uninstall() && $this->uninstallDB();
        return parent::uninstall();
    }

    public function installDB()
    {
        $return = true;


        $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'korta_payment_data` (
                `id` int(11) NOT NULL AUTO_INCREMENT,

                `demo_mode` varchar(255) ,
                `merchant_id` varchar(255) ,
                `terminal_id` varchar(255) ,
                `secret_code` varchar(255) ,
                `success_url` varchar(255) ,
                `cancel_url` varchar(255),
                PRIMARY KEY (`id`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'
        );
        return $return;
    }

    public function uninstallDB($drop_table = true)
    {
        // $ret = true;
        // if ($drop_table) {
        //     $ret &=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'korta`');
        // }
        //
        // return $ret;
        return;
    }

    public function getContent()
    {



        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $demo_mode = $_POST['demo_mode'];
          $merchant_id = $_POST['merchant_id'];
          $terminal_id = $_POST['terminal_id'];
          $secret_code = $_POST['secret_code'];
          $cancel_url = $_POST['cancel_url'];
          $updateform = $_POST['updateform'];
          if($updateform=="0"){
            Db::getInstance()->insert('korta_payment_data', array(
                    'demo_mode' => pSQL($demo_mode),
                    'merchant_id' => pSQL($this->encryptIt($merchant_id)),
                    'terminal_id' => pSQL($this->encryptIt($terminal_id)),
                    'secret_code' => pSQL($this->encryptIt($secret_code)),
                    'cancel_url' => pSQL($cancel_url),
                ));
          }
          else {
            Db::getInstance()->update('korta_payment_data', array(
              'demo_mode' => $demo_mode,
              'merchant_id' => $this->encryptIt($merchant_id),
              'terminal_id' => $this->encryptIt($terminal_id),
              'secret_code' => $this->encryptIt($secret_code),
              'cancel_url' => $cancel_url,
            ), 'id = 1' );
          }


        }

                $this->html .= $this->renderForm();
                return $this->html;
    }

    public function processSaveCustomText()
    {


        $images = $_FILES;
        // print_r($_POST['checkBoxShopAsso_configuration']);
        $userid = Context::getContext()->employee;
        foreach ( $images as $key => $value) {
            if($_FILES[$key]['name'] !='' && $_FILES[$key]['size'] > 0 ){
                if(isset($_POST['checkBoxShopAsso_configuration'])){
                   // echo "s</b>";
                    foreach ($_POST['checkBoxShopAsso_configuration'] as $k => $value) {
                        // echo $k;
                        $info = new Korta();
                        $info->image = $_FILES[$key]['name'];
                        $info->id_shop = $k;
                        $info->user_id = $userid->id;
                        $saved = $info->save();
                    }

                }else{
                    $info = new Korta();
                    $info->image = $_FILES[$key]['name'];
                    $info->id_shop =  $this->context->shop->id;
                    $info->user_id = $userid->id;
                    $saved = $info->save();
                }
            }
        }
        // exit();
        return $saved;
    }






    protected function renderForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('korta_payment_data', 'k');
        $sql->where('k.id = 1');
        $result = Db::getInstance()->executeS($sql);
        $button_title = 'Save';
        if($result){
          $button_title='Update';
        }

        $options_demo_mode = array(
            array(
              'id_option' => 'yes',
              'name' => 'Yes'
            ),
            array(
              'id_option' => 'no',
              'name' => 'No'
            ),
          );
        $fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->trans('Korta Payment Configuration', array(), 'Modules.Korta'),
            ),
            'input' => array(
                array(
                        'type' => 'select',
                        'label' => $this->trans('Enable Demo Mode:', array(), 'Modules.Korta'),
                        'name' => 'demo_mode',
                        'options' => array(
                            'query' => $options_demo_mode,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),

                array(
                        'type' => 'text',
                        'label' => $this->trans('Korta Merchant ID:', array(), 'Modules.Korta'),
                        'name' => 'merchant_id',
                        'required'=>true,
                        'placeholder'=>'Korta Merchant ID'
                    ),
                array(
                        'type' => 'text',
                        'label' => $this->trans('Korta Terminal ID:', array(), 'Modules.Korta'),
                        'name' => 'terminal_id',
                        'required'=>true,
                        'placeholder'=>'Korta Terminal ID'
                    ),
                array(
                        'type' => 'text',
                        'label' => $this->trans('Secret Code:', array(), 'Modules.Korta'),
                        'name' => 'secret_code',
                        'required'=>true,
                        'placeholder'=>'Secret Code'
                    ),
                // array(
                //         'type' => 'text',
                //         'label' => $this->trans('Success URL:', array(), 'Modules.Korta'),
                //         'name' => 'success_url',
                //         'placeholder'=>'Success URL'
                //     ),
                array(
                        'type' => 'text',
                        'label' => $this->trans('Cancel URL:', array(), 'Modules.Korta'),
                        'name' => 'cancel_url',
                        'placeholder'=>'Cancel URL'
                    ),
                    array(
                            'type' => 'hidden',
                            'name' => 'updateform',
                        ),
            ),
            'submit' => array(
                'title' => $this->trans($button_title, array(), 'Admin.Actions'),
            ),
            'buttons' => array(
                array(
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                    'title' => $this->trans('Back to list', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-back'
                )
            )
        );

        if (Shop::isFeatureActive() && Tools::getValue('id_korta') == false) {
            $fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->trans('Shop association', array(), 'Admin.Global'),
                'name' => 'checkBoxShopAsso_theme'
            );
        }


        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'ps_korta';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'saveps_customtext';
        if($result){
            $helper->fields_value = array(
              'demo_mode' =>$result[0]['demo_mode'],
              'merchant_id' =>$this->decryptIt($result[0]['merchant_id']),
              'terminal_id' =>$this->decryptIt($result[0]['terminal_id']),
              'secret_code' =>$this->decryptIt($result[0]['secret_code']),
              'success_url' =>$result[0]['success_url'],
              'cancel_url' =>$result[0]['cancel_url'],
              'updateform' =>"1"
            );
        }
        else{
          $helper->fields_value = array(
            'demo_mode' =>"yes",
            'merchant_id' =>"",
            'terminal_id' =>"",
            'secret_code' =>"",
            'success_url' =>"",
            'cancel_url' =>"",
            'updateform' =>"0"
          );
        }
        // $helper->fields_value = $this->getFormValues();

        return $helper->generateForm(array(array('form' => $fields_form)));
    }

    public function getFormValues()
    {

        $fields_value = array();
        $id_korta = 1;

        foreach (Language::getLanguages(false) as $lang) {
            $info = new Korta((int)$id_korta);

            $fields_value['image'][(int)$lang['id_lang']] = $info->text[(int)$lang['id_lang']];
        }

        $fields_value['id_korta'] = $id_korta;



        return $fields_value;
    }
    public function hookPaymentOptions($params)
    {

        if (!$this->active) {
            return;
        }


        $currency_code = $this->checkCurrency($params['cart']);
        $cart = $this->context->cart;
        $products = $cart->getProducts(true);

        $address = new Address(intval($params['cart']->id_address_delivery));
        $customer = new Customer($cart->id_customer);

        $cus_name = $customer->firstname." ".$customer->lastname;

        $cus_company = $customer->company;
        $cus_address = $address->address1;
        $cus_address2 = $address->address2;
        $cus_city = $address->city;
        $cus_zip = $address->postcode;
        $cus_country = $address->country;

        $cus_state = State::getNameById($address->id_state);

        $cus_phone = $address->phone;
        $cus_email = $customer->email;


        global $cookie;
        $lang_iso_code = Language::getIsoById( (int)$cookie->id_lang );

        $total_price = (float)($cart->getOrderTotal(true, Cart::BOTH));

        $description = "";

        foreach ($products as &$product) {
            $description .= $product['name'].",";

        }
        $description = substr($description, 0, -1);

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('korta_payment_data', 'k');
        $sql->where('k.id = 1');
        $result = Db::getInstance()->executeS($sql);
        $merchantid = $this->decryptIt($result[0]['merchant_id']);
        $secretid = $this->decryptIt($result[0]['secret_code']);
        $terminalid = $this->decryptIt($result[0]['terminal_id']);

        $test_var = 'TEST';
        if($result[0]['demo_mode']=="no"){
          $test_var = '';
        }

        $checksum_string = $total_price.$currency_code.$merchantid.$terminalid.$description.$secretid.$test_var;

        $checksum = md5($checksum_string);
        $checksum = "UTF-8:".$checksum;

        $success_return_url= $this->context->link->getModuleLink($this->name, 'validation', array(), true);
        $cancel_return_url = $success_return_url;
        if($result[0]['success_url'] && $result[0]['success_url'] != ""){
          $success_return_url = $result[0]['success_url'];
        }
        if($result[0]['cancel_url'] && $result[0]['cancel_url'] != ""){
          $cancel_return_url = $result[0]['cancel_url'];
        }

        $newOption = new PaymentOption();

        $newOption->setModuleName("ps_korta")
                ->setCallToActionText($this->trans('Pay with Korta', array(), 'Modules.Korta'))
                  ->setAction("https://netgreidslur.korta.is/testing/")
                  ->setInputs([
                    ['name' =>'amount',
                                'type' =>'hidden',
                                'value' =>$total_price,
                              ],
                    ['name' =>'currency',
                                'type' =>'hidden',
                                'value' =>$currency_code,
                              ],
                    ['name' =>'merchant',
                                'type' =>'hidden',
                                'value' =>$merchantid,
                              ],
                    ['name' =>'terminal',
                                'type' =>'hidden',
                                'value' =>$terminalid,
                              ],
                    ['name' =>'description',
                                'type' =>'hidden',
                                'value' =>$description,
                              ],
                    ['name' =>'checkvaluemd5',
                                'type' =>'hidden',
                                'value' =>$checksum,
                              ],
                    ['name' =>'callbackurl',
                                'type' =>'hidden',
                                'value' =>$success_return_url,
                              ],
                    ['name' =>'cancelurl',
                                'type' =>'hidden',
                                'value' =>$cancel_return_url,
                              ],
                    ['name' =>'refermethod',
                                'type' =>'hidden',
                                'value' =>'POST',
                              ],
                    ['name' =>'refertarget',
                                'type' =>'hidden',
                                'value' =>'_self',
                              ],
                    ['name' =>'downloadurl',
                                'type' =>'hidden',
                                'value' =>$success_return_url,
                              ],
                    ['name' =>'lang',
                                'type' =>'hidden',
                                'value' =>$lang_iso_code,
                              ],

                    ['name' =>'name',
                                'type' =>'hidden',
                                'value' =>$cus_name,
                              ],
                    ['name' =>'company',
                                'type' =>'hidden',
                                'value' =>$cus_company,
                              ],
                    ['name' =>'address',
                                'type' =>'hidden',
                                'value' =>$cus_address,
                              ],
                    ['name' =>'address2',
                                'type' =>'hidden',
                                'value' =>$cus_address2,
                              ],
                    ['name' =>'city',
                                'type' =>'hidden',
                                'value' =>$cus_city,
                              ],

                    ['name' =>'zip',
                                'type' =>'hidden',
                                'value' =>$cus_zip,
                              ],
                    ['name' =>'state',
                                'type' =>'hidden',
                                'value' =>$cus_state,
                              ],
                    ['name' =>'encoding',
                                'type' =>'hidden',
                                'value' =>'UTF-8',
                              ],
                    ['name' =>'country',
                                'type' =>'hidden',
                                'value' =>$cus_country,
                              ],
                    ['name' =>'phone',
                                'type' =>'hidden',
                                'value' =>$cus_phone,
                              ],
                    ['name' =>'email',
                                'type' =>'hidden',
                                'value' =>$cus_email,
                              ],
                    ['name' =>'email2',
                                'type' =>'hidden',
                                'value' =>$cus_email,
/*  Begin - Additional readonly and terms field commands */
                              ],
					['name' =>'readonly',
								'type' =>'hidden',
								'value' =>'Y',
							  ],
					['name' =>'terms',
								'type' =>'hidden',
								'value' =>'Y',
							  ],
/*  End - Additional readonly and terms field commands */
                    ])
                ->setAdditionalInformation($this->fetch('module:ps_korta/views/templates/hook/ps_korta_payment.tpl'));
        $payment_options = [
            $newOption,
        ];

        return $payment_options;
    }
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return $currency_module['iso_code'];
                }
            }
        }
        return "USD";
    }
    public function hookPaymentReturn($params)
    {

      global $cookie;
      $card_no = $cookie->card_no;
      $pay_date = $cookie->pay_date;
      $cardtype = $cookie->cardtype;

      $this->context->smarty->assign(
        array(
          'card_no' => $card_no,
          'pay_date' =>$pay_date,
          'cardtype' => $cardtype
      )
    );
    if($card_no !="" && $pay_date !="" && $cardtype !=""){
      return $this->display(__FILE__, 'ps_korta_payment_return.tpl');
    }
    else{
      return "";
    }

      // return $this->fetch('module:ps_wirepayment/views/templates/hook/ps_korta_payment_return.tpl');
    }

   function encryptIt( $q ) {
       $cryptKey  = 'qJB0rjhGtIn5vgh7887rc67UB1xG03efy8Cp';
       $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
       return( $qEncoded );

   }

   function decryptIt( $q ) {
       $cryptKey  = 'qJB0rjhGtIn5vgh7887rc67UB1xG03efy8Cp';
       $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
       return( $qDecoded );
   }

}
