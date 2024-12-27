<?php

/*
  Plugin Name: Купить в один клик
  Description: Плагин предосталяет пользователю возможности быстрой покупки. Плагин имеет страницу настроек для выбора необходимых данных от покупателя при быстрой покупке.
  Author: Moguta.CMS
  Version: 1.5.1
  Edition: CLOUD
 */

new BuyClick;

class BuyClick
{

    private static $lang = array(); // массив с переводом плагина
    private static $pluginName = ''; // название плагина (соответствует названию папки)
    private static $path = ''; //путь до файлов плагина
    private static $duplicate = array(); //масив с данными из карзины

    public function __construct()
    {

        mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации
        mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина
        mgAddAction('models_cart_addtocart', array(__CLASS__, 'operationsWithCart'), 1);
        mgAddShortcode('buy-click', array(__CLASS__, 'buyOneClick')); /* Инициализация шорткода [buy-click id="<?php echo $data['id']?>"] - доступен в любом HTML коде движка.   */

        self::$pluginName = PM::getFolderPlugin(__FILE__);
        self::$lang = PM::plugLocales(self::$pluginName);
        self::$path = PLUGIN_DIR . self::$pluginName;

        if (!URL::isSection('mg-admin')) { // подключаем CSS и JS плагина для всех страниц, кроме админки
            mgAddMeta('<link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />');
            mgAddMeta('<link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/jquery.fancybox.min.css" />');
            mgAddMeta('<script src="' . SITE . '/mg-core/script/jquery.fancybox.pack.js"></script>');
        }
        if (method_exists('MG', 'addAgreementCheckbox')) {
            mg::addAgreementCheckbox("mg-send-order-click-button", array(), 'addJSVariable', 'agreement_buy_click');
        }
        mgAddMeta('<script src="' . SITE . '/' . self::$path . '/js/buyclick.js"></script>');
        mgAddMeta('<script src="' . SITE . '/mg-core/script/jquery.maskedinput.min.js"></script>');
    }

    /**
     * Метод выполняющийся при активации палагина
     */
    public static function activate()
    {
        $lang = self::$lang;
        $option = MG::getSetting('buyClickOption');
        if (empty($option)) {
            $array = Array(
                'name' => 'true',
                'phone' => 'true',
                'email' => 'true',
                'address' => 'true',
                'payment' => '4',
                'delivery' => '3',
                'capcha' => 'false',
                'product' => 'true',
                'header' => $lang['BUY_CLICK_MODAL_PUBLIC_TITLE'],
                'button' => $lang['BUY_CLICK_BTN_PUBLIC_TITLE_DEFAULT'],
                'comment' => 'true',
            );

            MG::setOption(array('option' => 'buyClickOption', 'value' => addslashes(serialize($array))));
        }
    }

    /**
     * Метод выполняющийся перед генераццией страницы настроек плагина
     */
    static function preparePageSettings()
    {
        echo '   
      <link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />     
      <script>
        includeJS("' . SITE . '/' . self::$path . '/js/script.js");  
      </script> 
    ';

        $option = MG::getSetting('buyClickOption');
        $option = stripslashes($option);
        $options = unserialize($option);
    }

    /**
     * Выводит страницу настроек плагина в админке
     */
    static function pageSettingsPlugin()
    {
        $lang = self::$lang;
        $pluginName = self::$pluginName;
        $entity = self::getDelivery();
        $payment = self::getPayment();
        self::preparePageSettings();

        //получаем опцию buyClickOption в переменную option
        $option = MG::getSetting('buyClickOption');
        $option = stripslashes($option);
        $options = unserialize($option);

        include('pageplugin.php');
    }

    /**
     * Получает из БД информацию о доставках
     */
    static function getDelivery()
    {
        USER::AccessOnly('1,4', 'exit()');
        $entity = array();
        $res = DB::query("
      SELECT * 
      FROM `" . PREFIX . "delivery` 
    ");
        while ($row = DB::fetchAssoc($res)) {
            $entity[] = $row;
        }

        return $entity;
    }

    /**
     * Получает из БД информацию о методах оплаты
     */
    static function getPayment()
    {
        USER::AccessOnly('1,4', 'exit()');
        $payment = array();
        $resultPayment = DB::query("
      SELECT * 
      FROM `" . PREFIX . "payment` 
    ");
        while ($row = DB::fetchAssoc($resultPayment)) {
            $payment[] = $row;
        }

        return $payment;
    }

    /**
     * Обработчик шотркода вида  [buy-click id="<?php echo $data['id']?>" count="<?php echo $data['count']?>" variant = id="<?php echo $data['variants']?>"]
     * выполняется когда при генерации страницы встречается
     */
    static function buyOneClick($product)
    {
        $lang = self::$lang;
        if (empty($product['id'])) {
            return false;
        }
        $option = MG::getSetting('buyClickOption');
        $option = stripslashes($option);
        $options = unserialize($option);

        if (in_array('min-order', PM::$listShortCode)) {
            if (MG::getSetting('min-order-sum') > 0) {
                $res = DB::query('SELECT price_course FROM ' . PREFIX . 'product WHERE id = ' . DB::quoteInt($product['id']));
                if ($row = DB::fetchAssoc($res)) {
                    if ($row['price_course'] < MG::getSetting('min-order-sum')) return false;
                }
            }
        }

        if (method_exists('MG', 'enabledStorage') && MG::enabledStorage()) {
            if (!isset($product['count'])) {
                $res = DB::query('SELECT `count` FROM `' . PREFIX . 'product_on_storage` WHERE `product_id`=' . DB::quote($product['id']) . ' AND `variant_id` > 0 AND `count` <> 0');
                if (DB::numRows($res) == 0) {
                    $res = DB::query('SELECT `count` FROM `' . PREFIX . 'product_on_storage` WHERE `product_id`=' . DB::quote($product['id']) . ' AND `variant_id` = 0 AND `count` <> 0');
                    if (DB::numRows($res) == 0) {
                        return false;
                    }
                }
            } elseif ($product['count'] == '0' && !isset($product['variant'])) {
                $res = DB::query('SELECT `count` FROM `' . PREFIX . 'product_on_storage` WHERE `product_id`=' . DB::quote($product['id']) . ' AND `variant_id` > 0 AND `count` <> 0');
                if (DB::numRows($res) == 0) {
                    return false;
                }
            } elseif ($product['count'] == '0' && !$product['variant']) {
                return false;
            }
        } else {
            if (!isset($product['count'])) {
                $res = DB::query('SELECT `count` FROM `' . PREFIX . 'product` WHERE `id`=' . DB::quote($product['id']));
                $count = DB::fetchArray($res);
                if ($count['count'] == '0') {
                    $res = DB::query('SELECT `count` FROM `' . PREFIX . 'product_variant` WHERE `product_id`=' . DB::quote($product['id']) . ' AND `count` <> 0');
                    if (DB::numRows($res) == 0) {
                        return false;
                    }
                }
            } elseif ($product['count'] == '0' && !isset($product['variant'])) {
                $res = DB::query('SELECT `count` FROM `' . PREFIX . 'product_variant` WHERE `product_id`=' . DB::quote($product['id']) . ' AND `count` <> 0');
                if (DB::numRows($res) == 0) {
                    return false;
                }
            } elseif ($product['count'] == '0' && !$product['variant']) {
                return false;
            }
        }
        if (method_exists('MG', 'addAgreementCheckbox')) {
            $flag = 'agreement_buy_click_flag';
        }
        $result = '<div class="main-btn buy-click  '.$flag.'">
                <a data-src="#modal-buy-click" href="javascript:;" class="mg-buy-click-button mg-plugin-btn js-open-modal-bclick"  data-product-id = ' . $product['id'] . ' data-captcha="'.($options['capcha'] === 'true' ? 'enabled' : 'disabled').'" style="display:none !important">'
            . $options['button'] . '
                </a>
              </div>';

        return $result;
    }

    /**
     *
     * Обработчик хука функции addToCart($id, $count = 1, $property = array('property' => '', 'propertyReal' => ''), $variantId = null)
     *
     */
    static function operationsWithCart($arg)
    {
        if (!empty($_POST['ajax']) && $_POST['ajax'] == 'buyclickflag' || $_GET['plugin']=='buy-click') {
            $model = new Models_Cart;
            self::$duplicate = array(
                'propertySetArray' => isset($_SESSION['propertySetArray']) ? $_SESSION['propertySetArray'] : array(),
                'cart' => isset($_SESSION['cart']) ? $_SESSION['cart'] : array());
            // очищаем корзину - все кроме нужного товара
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] != $arg['args'][0] || 
                    $item['variantId'] != $arg['args'][3] || 
                    htmlspecialchars_decode(htmlspecialchars_decode(htmlspecialchars_decode($item['property']))) != $arg['args'][2]['property']
                    ){
                    $model->delFromCart($item['id'], $item['property'], $item['variantId']);
                }
                if ($item['id'] == $arg['args'][0] && 
                    $item['variantId'] == $arg['args'][3] && 
                    (
                        htmlspecialchars_decode(htmlspecialchars_decode(htmlspecialchars_decode($item['property']))) == $arg['args'][2]['property'] && 
                        htmlspecialchars_decode(htmlspecialchars_decode(htmlspecialchars_decode($item['propertyReal']))) == $arg['args'][2]['propertyReal'] || 
                        $item['property'] == $arg['args'][2]
                    )
                    ){
                    if($arg['args'][1]>0){
                      $_SESSION['cart'][$key]['count'] = $arg['args'][1];
                    }else{
                      $model->delFromCart($item['id'], $item['property'], $item['variantId']);
                    }
                }
            }

            if(count($_SESSION['cart'])>0){
              $result = self::orderOneClick();      // оформляем заказ
              $_SESSION['propertySetArray'] = self::$duplicate['propertySetArray'];
              $_SESSION['cart'] = self::$duplicate['cart'];
              if($result['status']=='error'){
                $data = (object)$result;
                echo json_encode($data);
                exit();
              }
            }else{
               $data->status='error';
               $data->msg='Товар отсутствует.';
               echo json_encode($data);
               exit();
            }

            $_SESSION['propertySetArray'] = self::$duplicate['propertySetArray'];
            $_SESSION['cart'] = self::$duplicate['cart'];

            // восстанавливаем содержимое корзины
            foreach ($_SESSION['cart'] as $item) {
                $propertyCompare = $arg['args'][2] == $item['property'] ? true : false;
                if ($item['id'] == $arg['args'][0] && $item['variantId'] == $arg['args'][3] && $propertyCompare) {
                    $model->delFromCart($item['id'], $item['property'], $item['variantId']);
                }
            }
        }
        return $arg['result'];
    }



    /*
     * Функция оформления заказа - добавление в БД и отправка писем администратору и покупателю.
     */

    static function orderOneClick()
    {
        // Модель для работы заказом.
        $option = MG::getSetting('buyClickOption');
        $option = stripslashes($option);
        $options = unserialize($option);
        $model = new Models_Order;
        $info = Array(
            'fio' => $_SESSION['infoClient']['name'],
            'email' => $options['email'] ? $_SESSION['infoClient']['email'] : "",
            'phone' => $options['phone'] ? $_SESSION['infoClient']['phone'] : "",
            'address' => $_SESSION['infoClient']['address'],
            'info' => $_SESSION['infoClient']['comment'] != '' ? $_SESSION['infoClient']['comment'] : "Быстрая покупка",
            'delivery' => $options['delivery'],
            'payment' => $options['payment'],
            'customer' => "fiz",
            'capcha' => $_SESSION['capcha']
        );
        if(MG::enabledStorage()){
            $res = DB::query('SELECT `storage` FROM `' . PREFIX . 'product_on_storage` WHERE `product_id`=' . DB::quote($_SESSION['cart'][1]['id']) . ' AND `count` <> 0 LIMIT 1');
            $row = DB::fetchAssoc($res);
            $info['storage'] = $row['storage'];
        }
        unset($_SESSION['infoClient']);
        $request = array();
        $newuser = false;
        $valid = $model->isValidData($info, $request, $newuser);
        $data = [];
        if (!is_string($valid)) {
            $data['status']='success';
            $model->addOrder();
        }else{
            $data['status']='error';
            if($valid===false) {
                $valid = "Невозможно оформить быстрый заказ на этот товар.";
            }
            $data['msg']=$valid;
        }
        return $data;
    }
}