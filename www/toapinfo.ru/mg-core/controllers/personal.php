<?php

/**
 * Контроллер: Personal
 *
 * Класс Controllers_Personal обрабатывает действия пользователей на странице личного кабинета.
 * - подготавливает данных пользователя для их отображения;
 * - обрабатывает запрос на изменения пароля;
 * - обрабатывает запрос на изменения способа оплаты;
 * - обрабатывает запрос на изменение данных пользователя.
 * 
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Personal extends BaseController {
  
  function __construct() {
    $lang = MG::get('lang');
    $settings = MG::get('settings');
    $this->lang = $lang;
    $status = 0;
    $orderColors = $paymentList = array();
    $pagination = '';
    $showAddressParts = false;
    if (User::isAuth()) {
      $order = new Models_Order;
      $status = 3;

      //обработка запроса на изменение данных пользователя
      if (URL::getQueryParametr('userData')) {
        $customer = URL::getQueryParametr('customer');
        $birthday = URL::getQueryParametr('birthday');
        if ($birthday) {
          $birthday = date('Y-m-d', strtotime(URL::getQueryParametr('birthday')));  
        }
        $userEmailParametr = URL::getQueryParametr('email');
        $userData = array(
          'name' => URL::getQueryParametr('name'),
          'sname' => URL::getQueryParametr('sname'),
          'pname' => URL::getQueryParametr('pname'),
          'birthday' => $birthday,
          'address' => URL::getQueryParametr('address'),
          'phone' => URL::getQueryParametr('phone'),
          'email' => !empty($userEmailParametr) ? $userEmailParametr : User::getThis()->login_email,
          'nameyur' => $customer == 'yur' ? URL::getQueryParametr('nameyur') : '',
          'adress' => $customer == 'yur' ? URL::getQueryParametr('adress') : '',
          'inn' => $customer == 'yur' ? URL::getQueryParametr('inn') : '',
          'kpp' => $customer == 'yur' ? URL::getQueryParametr('kpp') : '',
          'bank' => $customer == 'yur' ? URL::getQueryParametr('bank') : '',
          'bik' => $customer == 'yur' ? URL::getQueryParametr('bik') : '',
          'ks' => $customer == 'yur' ? URL::getQueryParametr('ks') : '',
          'rs' => $customer == 'yur' ? URL::getQueryParametr('rs') : '',
          'address_index' => URL::getQueryParametr('address_index'),
          'address_country' => URL::getQueryParametr('address_country'),
          'address_region' => URL::getQueryParametr('address_region'),
          'address_city' => URL::getQueryParametr('address_city'),
          'address_street' => URL::getQueryParametr('address_street'),
          'address_house' => URL::getQueryParametr('address_house'),
          'address_flat' => URL::getQueryParametr('address_flat'),
        );

        $tmp = array();
        foreach ($_REQUEST as $key => $value) {
          if(substr_count($key, 'op_') > 0) {
            $id = str_replace('op_', '', $key);
            $op = unserialize(stripslashes(MG::getsetting('userOp')));
            if($op[$id]['type'] == 'checkbox' && $value == 'on') {
              $tmp[$id] = 'true';
            } else {
              $tmp[$id] = $value;
            }
          }
        }
        if(empty($error)){
        }
        
        
        if (USER::update(User::getThis()->id, $userData)) {
          // $message = 'Данные успешно сохранены';
          $message = MG::restoreMsg('msg__pers_saved');
        } else {
          //$error = 'Не удалось сохранить данные '.$this->_newUserData['sname'];
          $error = MG::restoreMsg('msg__pers_data_fail').' '.$this->_newUserData['sname'];
        }
      }
      // Обработка запроса на изменения пароля.
      if (URL::getQueryParametr('chengePass')) {
        if (USER::auth(User::getThis()->login_email, URL::getQueryParametr('pass'))) {
          $person = new Models_Personal;
          $message = $person->changePass(URL::getQueryParametr('newPass'), User::getThis()->id);
        } else {
          $error = 'Неверный пароль';
          //$error = MG::restoreMsg('msg__pers_wrong_pass');
        }
      }

      // Обработка запроса на изменения способа оплаты.
      if (URL::getQueryParametr('changePaymentId')) {
        $paymentId = intval($_POST['changePaymentId']);  
        $orderId = intval($_POST['orderId']);

        $payment = $order->getPaymentMethod($paymentId, false);
        $orderData = $order->getOrder(' id = '.DB::quote($orderId));
        $orderData = $orderData[$orderId];        
        
        $orderContent = unserialize(stripslashes($orderData['order_content']));
        
        $oldRate = 0;

        $oldPayment = $orderData['payment_id'];
        $res = DB::query('SELECT rate FROM '.PREFIX.'payment WHERE id = '.DB::quoteInt($oldPayment));
        while ($row = DB::fetchAssoc($res)) {
          $oldRate = $row['rate'];
        }

        $_POST['email'] = $orderData['contact_email']; 
        $_POST['paymentId'] = $paymentId;
        $_POST['deliveryId'] = $orderData['delivery_id'];
        $_POST['orderItems'] = $orderContent;
        $_POST['usePlugins'] = 'true';
        $_POST['orderId'] = $orderId;
        $data = Models_Order::getOrderDiscount();

        $orderContent = [];
        foreach ($data['orderItems'] as $key => $value) {
          $orderContent[] = $value;
        }
        if(MG::getSetting('enableDeliveryCur') == 'true') {
          $orderData['delivery_cost'] = round($orderData['delivery_cost'] / (1 + $oldRate) * (1 + $payment['rate']), 2);
          $orderData['delivery_shop_curr'] = round($orderData['delivery_shop_curr'] / (1 + $oldRate) * (1 + $payment['rate']), 2);
        }
        $orderData['summ_shop_curr'] = round($orderData['summ_shop_curr'] / (1 + $oldRate) * (1 + $payment['rate']), 2);
        
        $status = $order->updateOrder(array(
          'payment_id' => $paymentId, 
          'summ' => $data['summ'], 
          'order_content' => addslashes(serialize($orderContent)), 
          'id' => $orderId,
          'delivery_cost' => $orderData['delivery_cost'],
          'summ_shop_curr' => $orderData['summ_shop_curr'],
          'delivery_shop_curr' => $orderData['delivery_shop_curr']));
        $result = array(
          'status' => $status,
          'comment' => 2,
          'orderStatus' => 3,
          'summ' => $data['summ'],
          'delivery' => $orderData['delivery_cost'],
          'currency' => MG::getSetting('currency')
        );

        echo json_encode($result);
        MG::disableTemplate();
        exit;
      }

      // Обработка AJAX запроса на закрытие заказа.
      if (URL::getQueryParametr('delOK')) {
        $comment = 'Отменено покупателем '.date('d.m.Y H:i').', по причине "'.URL::getQueryParametr('comment').'"' ;
        // Пересчитываем остатки продуктов из заказа.
        $order->refreshCountProducts(URL::getQueryParametr('delID'), 4);

        $res = DB::query('
          SELECT `comment` FROM `'.PREFIX.'order`
          WHERE id = '.DB::quote(URL::getQueryParametr('delID')).' AND user_email ='.DB::quote(User::getThis()->login_email));
        $res = DB::fetchAssoc($res);
        $comment = $res['comment'].="\n".$comment;

        $res = DB::query('
          UPDATE `'.PREFIX.'order`
          SET close_date = now(), status_id = 4, comment = '.DB::quote($comment).'
          WHERE id = '.DB::quote(URL::getQueryParametr('delID')).' AND user_email ='.DB::quote(User::getThis()->login_email));

        if ($res) {
          $status = false;
        }

          $comment = "<b>Комментарий: ".$comment."</b>";
        if (class_exists('statusOrder')) {
          $dbQuery = DB::query('SELECT `status` FROM `'.PREFIX.'mg-status-order` '
            . 'WHERE `id_status`=4');
          if ($dbRes = DB::fetchArray($dbQuery)) {
            $status = $dbRes['status'];
          }
        }
        if (!$status) {
          $status = $order->getOrderStatus(array('status_id' => 4));
        }
        $result = array(
          'status' => $status,
          'comment' => $comment,
          'orderStatus' => $status
        );

        $order->sendMailOfUpdateOrder(URL::getQueryParametr('delID'), URL::getQueryParametr('comment'), $status);

        echo json_encode($result);
        MG::disableTemplate();
        exit;
      }
      
      // Отображение данных пользователя.
      //$orderArray = $order->getOrder('user_email = "'.User::getThis()->email.'"');
      $page=!empty($_REQUEST["page"])?$_REQUEST["page"]:0;
      $perPage = MG::getSetting('ordersPerPageForUser')?:10;
      $sql =
            "SELECT * FROM `".PREFIX."order` "
          . "WHERE user_email=".DB::quote(User::getThis()->login_email)." "
          . "ORDER BY `add_date` DESC";
      $nav = new Navigator($sql, $page, $perPage);
      $orderArray = $nav->getRowsSql();
      $pagination = $nav->getPager();
      $statusOrder = array();
      if (class_exists('statusOrder')) {

        $dbQuery = DB::query("SHOW COLUMNS FROM `".PREFIX."mg-status-order` LIKE 'bgColor'");

        if(!$row = DB::fetchArray($dbQuery)) {//старая версия плагина
          $dbQuery = DB::query('SELECT `id_status`, `status` FROM `'.PREFIX.'mg-status-order`');
          while ($dbRes = DB::fetchArray($dbQuery)) {
            $statusOrder[$dbRes['id_status']] = $dbRes['status'];
          }
        }
        else{//новая версия плагина
          $dbQuery = DB::query('SELECT `id_status`, `status`, `bgColor`, `textColor` FROM `'.PREFIX.'mg-status-order`');
          while ($dbRes = DB::fetchArray($dbQuery)) {
            $statusOrder[$dbRes['id_status']] = $dbRes['status'];
            if (strlen($dbRes['bgColor']) > 3) {
              $orderColors[$dbRes['id_status']]['bgColor'] = $dbRes['bgColor'];
            }
            if (strlen($dbRes['textColor']) > 3) {
              $orderColors[$dbRes['id_status']]['textColor'] = $dbRes['textColor'];
            }
          }
        }
      }
      $propertyOrder = unserialize(stripcslashes(MG::getSetting('propertyOrder')));
      $downloadInvoice = $propertyOrder['downloadInvoice'];
      
      if (is_array($orderArray)) {
        foreach ($orderArray as $orderId => $orderItems) {
          if(isset($showYrlPayment)){
            unset($showYrlPayment);
          }
          $orderArray[$orderId]['string_status_id'] = isset($statusOrder[$orderItems['status_id']]) ? $statusOrder[$orderItems['status_id']] : $order->getOrderStatus($orderItems);
          
          $paymentArray = Models_Payment::getPaymentById($orderItems['payment_id']);
          $paymentName = strpos($paymentArray['code'], 'old#') === 0 ? $paymentArray['name'] : $paymentArray['public_name'];
          $orderArray[$orderId]['name'] = $paymentName.mgGetPaymentRateTitle($paymentArray['rate']);
          $orderArray[$orderId]['paided'] = $order->getPaidedStatus($orderItems);
          // Проверяем, не запрещено ли скачивать счет для юр лица.
          if(!empty($orderItems['yur_info'])){
            $yur_info = unserialize(stripcslashes($orderItems['yur_info']));

            if(
              !empty($downloadInvoice) && $downloadInvoice == 3 ||
               ($downloadInvoice == 2 && $orderItems['status_id'] == 0
            )){
              $showYrlPayment = 0;
            }else{
              $showYrlPayment = 1;
            } 
            $orderArray[$orderId]['yur_info'] = addslashes(serialize($yur_info));
          }
          /*
            Показываем ли кнопку перейти к оплате
              1) Если заказ на Юр. лицо и резрешено скачивать счет юр лицу (проверка выше) 
              3) Если оплата на физика, включена опция "Оплата после подтверждения менеджером" и заказ разрешено оплачивать
              2) Если у нас оплата на физика и выключена опция "Оплата после подтверждения менеджером"
              4) Если нет полей настройки "Оплата после подтверждения менеджером" или "Скачивание счета"
            */
            $showPaymentForm = 0;
            if( (isset($showYrlPayment) && $showYrlPayment == 1) ){
              $showPaymentForm = 1;
            }
            if( (!isset($showYrlPayment) && isset($propertyOrder['paymentAfterConfirm']) && $propertyOrder['paymentAfterConfirm'] == 'true' && $orderItems['approve_payment'] == 1 )){
              $showPaymentForm = 1;
            }
            if( (!isset($showYrlPayment) && isset($propertyOrder['paymentAfterConfirm']) && $propertyOrder['paymentAfterConfirm'] == 'false' ) ){
              $showPaymentForm = 1;
            }
            if( (!isset($showYrlPayment) && !isset($propertyOrder['paymentAfterConfirm'])) ) {
              $showPaymentForm = 1;
            }
            if (MG::isNewPayment() && empty($paymentArray['plugin'])) {
              $showPaymentForm = 0;
            }
          $orderArray[$orderId]['showPaymentForm'] = $showPaymentForm;
        }
      }
      
      if (!User::getThis()->activity) {
        $status = 2;
        unset($_SESSION['user']);
        unset($_SESSION['userAuthDomain']);
      }

      if (User::getThis()->blocked) {
        $status = 1;
        unset($_SESSION['user']);
      }
      $paymentListTemp = $order->getPaymentBlocksMethod();
      $paymentList[] = array();

      if (User::getThis()->inn) {
        $userType = 'yur';
      }
      else{
        $userType = 'fiz';
      }

      foreach ($paymentListTemp as $item) {
        if ($item['activity'] != '0') {
          if($userType=="yur" && $item['permission'] == "fiz") {
            continue;
          }

          if($userType=="fiz" && $item['permission'] == "yur") {
            continue;
          }
          $item['name'] .= mgGetPaymentRateTitle($item['rate']);          
          $paymentList[$item['id']] = $item;
        }
      }

      $showAddressParts = false;
      $res = DB::query('SELECT id FROM '.PREFIX.'delivery WHERE address_parts > 0');
      if($row = DB::fetchAssoc($res)) {
        $showAddressParts = true;
      }
    }
    if(!empty($orderArray)){
      foreach ($orderArray as $key => $value) {
        if(!empty($value['comment'])) $orderArray[$key]['comment'] = MG::replaceBBcodes($value['comment']);

        // флаг указывает имеются ли в заказе электронные товары
        $electro = false;
        $orderProducts = unserialize(stripslashes($value['order_content']));
        foreach ($orderProducts as $orderProduct) {
          if ($orderProduct['electro']) {
            $electro = true;
            break;
          }
        }
        $orderArray[$key]['electro'] = $electro;
      }
    }

    $this->data = array(
      'error' => !empty($error) ? $error : '', // Сообщение об ошибке.
      'message' => !empty($message) ? $message : '', // Информационное сообщение.
      'status' => !empty($status) ? $status : '', // Статус пользователя.
      'userInfo' => User::getThis(), // Информация о пользователе.
      'orderInfo' => !empty($orderArray) ? $orderArray : '', // Информация о заказе.
      'orderColors' => $orderColors,
      'pagination' => $pagination,
      'currency' => $settings['currency'],
      'paymentList' => $paymentList,
      'meta_title' => 'Личный кабинет',
      'meta_keywords' => "заказы,личные данные, личный кабинет",
      'meta_desc' => "В личном кабинете нашего сайта вы сможете отслеживать состояние заказов и менять свои данные",
      'assocStatusClass'=> array('dont-confirmed', 'get-paid', 'paid', 'in-delivery', 'dont-paid', 'performed', 'processed'), // цветная подсветка статусов
      'showAddressParts' => $showAddressParts
    );
  }
}
