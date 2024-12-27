<?php

/*
  Plugin Name: Обратный звонок
  Description: Плагин является заготовкой для разработчиков плагинов определяется шорткодом [back-ring], имеет страницу настроек, создает в БД таблицу для дальнейшей работы, использует собственный файл локали, свой  CSS и JS скрипы.
  Author: Avdeev Mark
  Version: 1.4.24
  Edition: CLOUD
 */

new BackRing;

class BackRing
{

  private static $lang = array(); // массив с переводом плагина 
  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = ''; //путь до файлов плагина 

  public function __construct()
  {

    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации  
    mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина  
    mgAddShortcode('back-ring', array(__CLASS__, 'handleShortCode')); // Инициализация шорткода [back-ring] - доступен в любом HTML коде движка.    

    self::$pluginName = PM::getFolderPlugin(__FILE__);
    $explode = explode(str_replace('/', DS, PLUGIN_DIR), dirname(__FILE__));
    if (strpos($explode[0], 'mg-templates') === false) {
      self::$path = str_replace('\\', '/', PLUGIN_DIR.DS.$explode[1]);
    } else {
      $templatePath = str_replace('\\', '/', $explode[0]);
      $templatePathParts = explode('/', $templatePath);
      $templatePathParts = array_filter($templatePathParts, function($pathPart) {
        if (trim($pathPart)) {
          return true;
        }
        return false;
      });
      $templateName = end($templatePathParts);
      self::$path = 'mg-templates/'.$templateName.'/mg-plugins/'.$explode[1];
    }
    self::$lang = PM::plugLocales(self::$pluginName);

    include('mg-admin/locales/' . MG::getSetting('languageLocale') . '.php');
    $lang = array_merge($lang, self::$lang);
    self::$lang = $lang;

    if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
      mgAddMeta('<link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />');
    }
    if (URL::isSection('mg-admin')) {
      MG::addInformer(array('count' => self::getEntityActive(), 'class' => 'count-wrap', 'classIcon' => 'fa-phone', 'isPlugin' => true, 'section' => 'back-ring', 'priority' => 80));
    }

    mgAddMeta('<script src="' . SITE . '/' . self::$path . '/js/backring.js"></script>');
//   mgAddMeta('<link rel="stylesheet" href="'.SITE.'/mg-admin/design/css/jquery-ui.css">');
    //  mgAddMeta('<script src="'.SITE.'/mg-core/script/jquery-ui-1.10.3.custom.min.js"></script>');
    mgAddMeta('<script src="' . SITE . '/' . self::$path . '/js/jquery.maskedinput.min.js"></script>');
    // mgAddMeta('<script> var availableTags = null;</script>');

    /*if (method_exists('MG', 'addAgreementCheckbox')) {
      mg::addAgreementCheckbox('send-ring-button', array(), 'addRegistry');
    }*/

  }


  /**
   * Метод выполняющийся при активации палагина
   */
  static function activate()
  {
    self::createDateBase();
  }


  /**
   * Метод выполняющийся перед генераццией страницы настроек плагина
   */
  static function preparePageSettings()
  {
    echo '   
      <!--<link rel="stylesheet" href="' . SITE . '/mg-admin/design/css/jquery-ui.css">-->
      <link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />     
      <link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />  
      <script>
        includeJS("' . SITE . '/' . self::$path . '/js/script.js");  
        includeJS("' . SITE . '/mg-core/script/jquery.maskedinput.min.js");  
      </script> 
    ';

    $option = MG::getSetting('back-ring-option');
    $option = stripslashes($option);
    $options = unserialize($option);
    $arrayCity = explode(',', $options['city_list']);
    $jsArrayCity = json_encode($arrayCity);
    echo '<script> var availableTags = ' . $jsArrayCity . ';</script>';
  }


  /**
   * Создает таблицу плагина в БД
   */
  static function createDateBase()
  {
    $tableName = '`'.PREFIX.self::$pluginName.'`';
    $checkTableExistsSql = 'SHOW TABLES LIKE '.str_replace('`', '"', $tableName);
    $checkTableExistsResult = DB::query($checkTableExistsSql);
    if (!DB::fetchAssoc($checkTableExistsResult)) {
      $createTableSql = 'CREATE TABLE IF NOT EXISTS '.
        $tableName.' '.
        '('.
          '`id` int(11) NOT NULL AUTO_INCREMENT COMMENT "Порядковый номер записи", '.
          '`type` varchar(255) NOT NULL COMMENT "Тип записи", '.
          '`add_datetime` DATETIME NOT NULL COMMENT "Дата добавления", '.
          '`name` text NOT NULL COMMENT "Имя", '.
          '`comment` text NOT NULL COMMENT "Комментарий", '.
          '`phone` text NOT NULL COMMENT "Телефон",  '.
          '`city_id` text NOT NULL COMMENT "Город", '.
          '`mission` text NOT NULL COMMENT "Цель", '.
          '`date_callback` DATE NOT NULL COMMENT "Дата для перезвона", '.
          '`time_callback` varchar(255) NOT NULL COMMENT "Время для перезвона", '.
          '`status_id` int(11) NOT NULL COMMENT "Статус", '.
          '`sort` int(11) NOT NULL COMMENT "Порядок", '.
          '`invisible` int(1) NOT NULL COMMENT "Видимость", '.
          'PRIMARY KEY (`id`)'
        .') ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
      DB::query($createTableSql);

      $truncateTableSql = 'TRUNCATE '.$tableName;
      DB::query($truncateTableSql);

      $defaultSettings = [
        'header' => 'Обратный звонок',
        'name' => 'true',
        'comment' => 'true',
        'period' => 'true',
        'caphpa' => 'false',
        'date' => 'true',
        'agreement' => 'true',
        'from' => '09',
        'to' => '18',
        'use_phone_prefix' => 'true',
        'phone_prefix' => '+7',
      ];

      MG::setOption(array('option' => 'back-ring-option', 'value' => addslashes(serialize($defaultSettings))));
      MG::setOption(array('option' => 'countPrintRowsBackRing', 'value' => 20));
    }
  }

  /**
   * Выводит страницу настроек плагина в админке
   */
  static function pageSettingsPlugin()
  {
    $lang = self::$lang;
    $pluginName = self::$pluginName;

    //получаем опцию back-ringOption в переменную option
    $option = MG::getSetting('back-ring-option');
    $option = stripslashes($option);
    $options = unserialize($option);


    self::preparePageSettings();
    $status = array('null' => 'Не выбрано', 1 => 'Ожидает', 2 => 'Не дозвониться', 3 => 'Завершен');

    //фильтры 
    $property = array(
      'id' => array(
        'type' => 'text',
        'label' => 'Номер заявки',
        'value' => !empty($_POST['id']) ? $_POST['id'] : null,
      ),

      'status_id' => array(
        'type' => 'select',
        'option' => $status,
        'selected' => (!empty($_POST['status_id']) || $_POST['status_id'] === '0') ? $_POST['status_id'] : 'null', // Выбранный пункт (сравнивается по значению)
        'label' => 'Статус'
      ),

      'sorter' => array(
        'type' => 'hidden', //текстовый инпут
        'label' => 'сортировка по полю',
        'value' => !empty($_POST['sorter']) ? $_POST['sorter'] : null,
      ),
    );


    if (isset($_POST['applyFilter'])) {
      $property['applyFilter'] = array(
        'type' => 'hidden', //текстовый инпут
        'label' => 'флаг примения фильтров',
        'value' => 1,
      );
    }


    $filter = new Filter($property);

    $arr = array(
      'id' => !empty($_POST['id']) ? $_POST['id'] : null,
      'status_id' => (!empty($_POST['status_id']) || $_POST['status_id'] === '0') ? $_POST['status_id'] : 'null',
    );

    $userFilter = $filter->getFilterSql($arr, explode('|', $_POST['sorter']));

    $sorterData = explode('|', $_POST['sorter']);

    if ($sorterData[1] > 0) {
      $sorterData[3] = 'desc';
    } else {
      $sorterData[3] = 'asc';
    }


    $page = !empty($_POST["page"]) ? $_POST["page"] : 0;//если был произведен запрос другой страницы, то присваиваем переменной новый индекс

    $countPrintRowsBackRing = MG::getSetting('countPrintRowsBackRing');

    if (empty($_POST['sorter'])) {
      if (empty($userFilter)) {
        $userFilter .= ' 1=1 ';
      }
      $userFilter .= "  ORDER BY `add_datetime` DESC";
    }

    $sql = "
      SELECT * FROM `" . PREFIX . self::$pluginName . "`
      WHERE " . $userFilter . "
    ";

    $navigator = new Navigator($sql, $page, $countPrintRowsBackRing); //определяем класс
    $entity = $navigator->getRowsSql();
    $pagination = $navigator->getPager('forAjax');
    $filter = $filter->getHtmlFilterAdmin();
    //фильтры конец

    $hours = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24');
    foreach ($hours as $item) {
      $selectHour .= "<option value='" . $item . "'>" . $item . "</option>";
    }

    $missions = explode(',', $options['mission_list']);
    foreach ($missions as $id => $item) {
      $selectMissions .= "<option value='" . $item . "'>" . $item . "</option>";
    }

    include('pageplugin.php');
  }


  /**
   * Получает из БД записи
   */
  static function getEntity($count = 1)
  {
    $result = array();
    $sql = "SELECT * FROM `" . PREFIX . self::$pluginName . "` ORDER BY sort ASC";
    if ($_POST["page"]) {
      $page = $_POST["page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс
    }
    $navigator = new Navigator($sql, $page, $count); //определяем класс
    $entity = $navigator->getRowsSql();
    $pagination = $navigator->getPager('forAjax');
    $result = array(
      'entity' => $entity,
      'pagination' => $pagination
    );
    return $result;
  }

  /**
   * Получает количество активных записей
   */
  static function getEntityActive()
  {
    $exist = false;
    $res = DB::query('SHOW TABLES LIKE "' . PREFIX . self::$pluginName . '"');
    if (DB::numRows($res)) {
      $exist = true;
    }


    if ($exist) {
      $sql = "SELECT count(id) as count FROM `" . PREFIX . self::$pluginName . "` WHERE invisible = 1 ORDER BY sort ASC";
      $res = DB::query($sql);
      if ($count = DB::fetchAssoc($res)) {
        return $count['count'];
      }
    }
    return 0;
  }

  /**
   * Обработчик шотркода вида [back-ring]
   * выполняется когда при генерации страницы встречается [back-ring]
   */
  static function handleShortCode()
  {
    $lang = PM::plugLocales(self::$pluginName);
    $option = MG::getSetting('back-ring-option');
    $option = stripslashes($option);
    $options = unserialize($option);

    if(isset($options['phone_prefix'])){
      $phone_prefix = trim($options['phone_prefix']);
    }else{
      $phone_prefix = '+7';
    }

    $hours = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24');
    $selectHour = "";
    foreach ($hours as $item) {
      if (isset($options['from']) && isset($options['to'])) {
      if ($item >= $options['from'] && $item <= $options['to']) {
        $selectHour .= "<option value='" . $item . "'>" . $item . "</option>";
      }
    }
    }

    $missions = !empty($options['mission_list']) ? explode(',', $options['mission_list']) : array();

    $selectMissions = '';

    foreach ($missions as $id => $item) {
      switch ($item) {
        case 'Доставка':
          $item = $lang['DELIVERY'];
          break;

        case 'Оплата':
          $item = $lang['PAYMENT'];
          break;

        case 'Технические вопросы':
          $item = $lang['TECHNICAL_SUPPORT'];
          break;

        default:
          # code...
          break;
      }
      $selectMissions .= "<option value='" . $item . "'>" . $item . "</option>";
    }

    /*if (method_exists('MG', 'addAgreementCheckbox')) {
      $agreement = mg::addAgreementCheckbox(
        'send-ring-button',
        array(
          'text' => $lang['TEXT_AGREEMENT'],
          'textLink' => $lang['TEXT_LINK_AGREEMENT']
        )
      );
    } else {
      $agreement = '';
    }*/


    if (isset($options['caphpa']) && $options['caphpa'] == 'true') {
      if (
        method_exists('MG', 'checkReCaptcha') &&
        MG::getSetting('useReCaptcha') == 'true' &&
        MG::getSetting('reCaptchaSecret') &&
        MG::getSetting('reCaptchaKey')
      ) {
        $captcha = '<li>' . MG::printReCaptcha(false) . '</li>';
      } else {
        $captcha = "<li>
          <div class='br-cap'>
            <div class='cap-left'>
                <img style='margin-top: 5px; border: 1px solid gray; background: url(" . PATH_TEMPLATE . "/images/cap.png)' src=\"" . SITE .(LANG == 'LANG'?'':LANG). "/captcha.html?t=".time()."\" width=\"140\" height=\"36\">
            </div>
            <div class='cap-right'>
                <span>" . $lang['CAPTCHA'] . ": </span>
                <input type='text' name='capcha' class='captcha'>
            </div>
          </div>
      </li>";
      }
    } else {
      $captcha = '';
    }


    $html = "
  <div class='wrapper-back-ring'><button type='submit' class='back-ring-button default-btn' data-init-name='initBackRing'>" . $lang['ORDER_RING'] . "</button></div>";
    $html .= "
    <div class='wrapper-modal-back-ring'>    
      <div class='header-modal-back-ring'>
        <span class='title-modal-back-ring'>
        " . $lang['TITLE'] . "
        </span>
          <button class='close-ring-button' aria-label='" . lang('close') . "'></button>
      </div>
      <div class='content-modal-back-ring'>
        <ul class='modal-ring-list'>
            <li style='" . ((isset($options['name']) && $options['name'] != 'true') ? 'display:none' : '') . "'>
                <span>" . $lang['NAME'] . ":</span>
                <input type='text' name='name' placeholder='" . $lang['NAME'] . "'>
            </li>
            <li>
                <span>" . $lang['PHONE'] . ":</span>
                <input type='text' name='phone' data-mask='".$phone_prefix."' placeholder='" . $lang['PHONE'] . "'>
            </li>";

           /*$html .= " <li style='" . ((isset($options['city']) && $options['city'] != 'true') ? 'display:none' : '') . "'>
                <span>" . $lang['CITY'] . ":</span>
                <input type='text' name='city_id' placeholder='" . $lang['CITY'] . "'>
            </li>";*/

           $html .= "  <li style='" . ((isset($options['comment']) && $options['comment'] != 'true') ? 'display:none' : '') . "'>
                <span>" . $lang['COMMENT'] . ":</span>
                <textarea class='comment-back-ring' name='comment' placeholder='" . $lang['COMMENT'] . "'></textarea>
            </li>";

            /* $html .= "
             <li style='" . ((isset($options['mission']) && $options['mission'] != 'true') ? 'display:none' : '') . "'>
                 <span>" . $lang['PURPOSE'] . ":</span>
                 <select name='mission'>" . $selectMissions . "</select>
             </li> ";
           */
            $html .= "
            <li style='" . ((isset($options['period']) && $options['period'] != 'true') ? 'display:none' : '') . "'>
                <span>" . $lang['PERIOD'] . "</span>
                <div class='br-date'>
                    <div class='left'>
                      <span class='br-date-text'>" . $lang['PERIOD_FROM'] . "</span> <select name='from'>" . $selectHour . "</select>
                      <span class='br-date-text'>" . $lang['PERIOD_TO'] . "</span> <select name='to'>" . $selectHour . "</select>
                    </div>
                    <div class='right' style='" . ((isset($options['date']) && $options['date'] != 'true') ? 'display:none' : '') . "'>
                         <input type='text' name='date_callback' placeholder='" . $lang['DATE'] . "'>
                    </div>
                </div>
                <div style='clear: both;'></div>
            </li>" . $captcha . "
        </ul>";
        if (isset($options['agreement']) && $options['agreement'] == 'true') {
          $html .= "<div class='agreement back-ring-agreement'>
          <label class='back-ring-agreement__label'>
            <input class='back-ring-agreement__input' id='backRingInput' type='checkbox'>&nbsp;
          </label>
          <span class='back-ring-agreement__text'>
            <span class='agreement__btn_ring'>".$lang['TEXT_AGREEMENT']."</span>
            <span class='agreement__btn_ring agreement__btn_open_ring enter-text'>".$lang['TEXT_LINK_AGREEMENT']."</span>
          </span>
          <div class='agreement__modal_ring'>
            <div>
              <button id='agreementCloseBtn' class='agreement__btn_ring agreement__btn_close_ring' aria-label='".$lang['CLOSE_MODAL']."'></button>
              <div class='agr-text'></div>
            </div>
          </div>
        </div>";
        }
        $html .= "<br><div class='utm-metka' style='display:none'>" . (isset($section) ? $section : '') . "</div>
        <button class='send-ring-button  red-btn default-btn' style='display:block;' aria-label='" . $lang['SEND_ORDER'] . "'>" . $lang['SEND_ORDER'] . "</button>
      </div>
  
    </div>";


    if(isset($options['phone_prefix'])){
      $data['phone_prefix'] = trim($options['phone_prefix']);
    }else{
      $data['phone_prefix'] = '+7';
    }

    $html .= '
      <script>
      $(".content-modal-back-ring input[name=date_callback]").datepicker({dateFormat: "yy-mm-dd"});
      if(typeof $.mask != "undefined") {
        var phone_prefix = '.$data['phone_prefix'].';
        $(".content-modal-back-ring input[name=phone]").mask(phone_prefix+" (999) 999-99-99");
      }
      </script>
    ';
    
    $arrayCity = !empty($options['city_list']) ? explode(',', $options['city_list']) : array();
    $jsArrayCity = json_encode($arrayCity);
    $html .= '<script> var availableTags = ' . $jsArrayCity . ';</script>';

    return $html;
  }

  /**
   * Сохраняет заявку в базу
   * @param array - массив данным заявки
   */
  static function saveOrderRing($array)
  {

    $sql = "
      INSERT INTO `" . PREFIX . self::$pluginName . "` 
      (`id`, `type`, `nameEntity`, `value`, `sort`, `invisible`)
      (1, 'img', 'name1', 'src1', 1,1)
    ";
    $res = DB::query($sql);
    if ($res) {
      return true;
    }
    return false;
  }

}
