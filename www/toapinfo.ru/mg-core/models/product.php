<?php

/**
 * Модель: Product
 *
 * Класс Models_Product реализует логику взаимодействия с товарами магазина.
 * - Добавляет товар в базу данных;
 * - Изменяет данные о товаре;
 * - Удаляет товар из базы данных;
 * - Получает информацию о запрашиваемом товаре;
 * - Получает продукт по его URL;
 * - Получает цену запрашиваемого товара по его id.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 *
 */
class Models_Product {

  public $storage = 'all';
  public $clone = false;

  /**
   * Добавляет товар в базу данных.
   * <code>
   * $array = array(
   *  'title' => 'title', // название товара
   *  'url' => 'link', // последняя часть ссылки на товар
   *  'code' => 'CN230', // артикул товара
   *  'price' => 100, // цена товара
   *  'old_price' => 200, // старая цена товара
   *  'image_url' => 1434653074061713.jpg, // последняя часть ссылки на изображение товара
   *  'image_title' => '', // title изображения товара
   *  'image_alt' => '', // alt изображения товара
   *  'count' => 77, // остаток товара
   *  'weight' => 5, // вес товара
   *  'cat_id' => 4, // ID основной категории товара
   *  'inside_cat' => '1,2', // дополнительные категории товаров
   *  'description' => 'descr', // описание товара
   *  'short_description' => 'short descr', // краткое описание товара
   *  'meta_title' => 'title', // seo название товара
   *  'meta_keywords' => 'title купить, CN230, title', // seo ключевые слова
   *  'meta_desc' => 'meta descr', // seo описание товара
   *  'currency_iso' => 'RUR', // код валюты товара
   *  'recommend' => 0, // выводить товар в блоке рекомендуемых
   *  'activity' => 1, // выводить товар
   *  'unit' => 'шт.', // единица измерения товара (если null, то используется единица измерения основной категории товара)
   *  'new' => 0, // выводить товар в блоке новинок
   *  'userProperty' => Array, // массив с характеристиками товара
   *  'related' => 'В-500-1', // артикулы связанных товаров
   *  'variants' => Array, // массив с вариантами товаров
   *  'related_cat' => null, // ID связанных категорий
   *  'lang' => 'default', // язык для сохранения
   *  'landingTemplate' => 'noLandingTemplate', // шаблон для лэндинга товара
   *  'ytp' => '', // строка с торговым предложением для лэндинга
   *  'landingImage' => 'no-img.jpg', // изображение для лэндинга
   *  'storage' => 'all' // склад товара
   * );
   * $model = new Models_Product();
   * $id = $model->addProduct($product);
   * echo $id;
   * </code>
   * @param array $array массив с данными о товаре.
   * @param bool $clone происходит ли клонирование или обычное добавление товара
   * @return int|bool в случае успеха возвращает id добавленного товара.
   */
  public function addProduct($array, $clone = false) {
    if(empty($array['title'])) {
      return false;
    }

    $userProperty = isset($array['userProperty'])?$array['userProperty']:array();
    $variants = !empty($array['variants']) ? $array['variants'] : array(); // варианты товара
    unset($array['userProperty']);
    unset($array['variants']);
    unset($array['count_sort']);
    unset($array['lang']);
    if(empty($array['id'])) {
      unset($array['id']);
    }

    if(empty($array['code'])) {
      $res = DB::query('SELECT max(id) FROM '.PREFIX.'product');
      $id = DB::fetchAssoc($res);
      $array['code'] = MG::getSetting('prefixCode').($id['max(id)']+1);
    }

    $result = array();

    $array['url'] = empty($array['url']) ? MG::translitIt($array['title']) : $array['url'];

    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');

    foreach ($array as $k => $v) {
      if(in_array($k, $maskField)) {
        $v = htmlspecialchars_decode($v);
        $array[$k] = htmlspecialchars($v);
      }
    }

    if (!empty($array['url'])) {
      $array['url'] = URL::prepareUrl($array['url']);
    }

    // Исключает дублирование.
    $dublicatUrl = false;
    $tempArray = $this->getProductByUrl($array['url']);
    if (!empty($tempArray)) {
      $dublicatUrl = true;
    }

    if(!empty($array['weight'])) {
     $array['weight'] = (double)str_replace(array(',',' '), array('.',''), $array['weight']);
    }else {
      $array['weight'] = 0;
    }

    if(!empty($array['price'])) {
      $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
    }
    $productActive = DB::query('SELECT `activity` FROM '.PREFIX.'category WHERE `id` = '.DB::quoteInt($array['cat_id']));
    $productActive = DB::fetchAssoc($productActive);
    $array['activity'] = $productActive['activity'];

    $array['sort'] = 0;
    $array['system_set'] = 1;

    // округляем количество до 2 знаков
    if (isset($array['count'])) {
      $array['count'] = round($array['count'],2);
    }


    unset($array['landingTemplate']);
    unset($array['landingColor']);
    unset($array['ytp']);
    unset($array['landingImage']);
    unset($array['landingSwitch']);

    unset($array['storage']);

    unset($array['color']);
    unset($array['size']);

    if(empty($array['currency_iso'])) $array['currency_iso'] = MG::getSetting('currencyShopIso');

    if (DB::buildQuery('INSERT INTO `'.PREFIX.'product` SET ', $array)) {
      $id = DB::insertId();

      // Если url дублируется, то дописываем к нему id продукта.
      if ($dublicatUrl) {
        $url_explode = explode('_', $array['url']);
        if (count($url_explode) > 1) {
          $array['url'] = str_replace('_'.array_pop($url_explode), '', $array['url']);
        }
        $updateArray = array(
          'id' => $id,
          'url' => $array['url'].'_'.$id,
          'sort' => $id,
          'description' => $array['description'],
        );
        if ($clone) {
          $updateArray['code'] = MG::getSetting('prefixCode').$id;
          $array['code'] = MG::getSetting('prefixCode').$id;
        }
        $this->updateProduct($updateArray);
      } else {
        $updateArray = array(
          'id' => $id,
          'url' => $array['url'],
          'sort' => $id,
          'description' => isset($array['description'])?$array['description']:'',
        );
        if ($clone) {
          $updateArray['code'] = MG::getSetting('prefixCode').$id;
          $array['code'] = MG::getSetting('prefixCode').$id;
        }
        $this->updateProduct($updateArray);
      }
      unset($landArr);

      $array['id'] = $id;
      $array['sort'] = (int)$id;
      $array['userProperty'] = $userProperty;
      $userProp = array();


      // Обновляем и добавляем варианты продукта.
      $this->saveVariants($variants, $id);
      $variants = $this->getVariants($id);
      foreach ($variants as $variant) {
        $array['variants'][] = $variant;
      }

      if(!empty($array['variants'][0]['code'])) {
        $arrayVariant = array('code' => $array['variants'][0]['code']);
        $this->fastUpdateProduct($array['id'], $arrayVariant);
      }

      $tempProd = $this->getProduct($id);
      $array['category_url'] = $tempProd['category_url'];
      $array['product_url'] = $tempProd['product_url'];

      $result = $array;
    }

    if (!isset($currencyShopIso)) {
      $currencyShopIso = MG::get('dbCurrency');
    }

    $this->updatePriceCourse($currencyShopIso, array($result['id']));

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Изменяет данные о товаре.
   * <code>
   * $array = array(
   *  'id' => 23, // ID товара
   *  'title' => 'title', // название товара
   *  'url' => 'link', // последняя часть ссылки на товар
   *  'code' => 'CN230', // артикул товара
   *  'price' => 100, // цена товара
   *  'old_price' => 200, // старая цена товара
   *  'image_url' => 1434653074061713.jpg, // последняя часть ссылки на изображение товара
   *  'image_title' => '', // title изображения товара
   *  'image_alt' => '', // alt изображения товара
   *  'count' => 77, // остаток товара
   *  'weight' => 5, // вес товара
   *  'cat_id' => 4, // ID основной категории товара
   *  'inside_cat' => '1,2', // дополнительные категории товаров
   *  'description' => 'descr', // описание товара
   *  'short_description' => 'short descr', // краткое описание товара
   *  'meta_title' => 'title', // seo название товара
   *  'meta_keywords' => 'title купить, CN230, title', // seo ключевые слова
   *  'meta_desc' => 'meta descr', // seo описание товара
   *  'currency_iso' => 'RUR', // код валюты товара
   *  'recommend' => 0, // выводить товар в блоке рекомендуемых
   *  'activity' => 1, // выводить товар
   *  'unit' => 'шт.', // единица измерения товара (если null, то используется единица измерения основной категории товара)
   *  'new' => 0, // выводить товар в блоке новинок
   *  'userProperty' => Array, // массив с характеристиками товара
   *  'related' => 'В-500-1', // артикулы связанных товаров
   *  'variants' => Array, // массив с вариантами товаров
   *  'related_cat' => null, // ID связанных категорий
   *  'lang' => 'default', // язык для сохранения
   *  'landingTemplate' => 'noLandingTemplate', // шаблон для лэндинга товара
   *  'ytp' => '', // строка с торговым предложением для лэндинга
   *  'landingImage' => 'no-img.jpg', // изображение для лэндинга
   *  'storage' => 'all' // склад товара
   * );
   * $model = new Models_Product();
   * $model->updateProduct($array);
   * </code>
   * @param array $array массив с данными о товаре.
   * @return bool
   */
  public function updateProduct($array) {
    $id = $array['id'];
    $count = 0;
    if (!empty($array['description'])) {
      $array['description'] = MG::moveCKimages($array['description'], 'product', $id, 'desc', 'product', 'description');
    }

    $userProperty = !empty($array['userProperty']) ? $array['userProperty'] : null; //свойства товара
    $variants = !empty($array['variants']) ? $array['variants'] : array(); // варианты товара
    $updateFromModal = !empty($array['updateFromModal']) ? true : false; // варианты товара

    unset($array['userProperty']);
    unset($array['variants']);
    unset($array['updateFromModal']);

    if (!empty($array['url'])) {
      $array['url'] = URL::prepareUrl($array['url']);
      $checkProductUrlSql = 'SELECT COUNT(`id`) as urlsCount FROM `'.PREFIX.'product` '.
        'WHERE `url` = '.DB::quote($array['url']).' '.
        'AND `id` != '.DB::quoteInt($id, true).';';
      $checkProductUrlQuery = DB::query($checkProductUrlSql);
      if ($checkProductUrlResult = DB::fetchAssoc($checkProductUrlQuery)) {
        if ($checkProductUrlResult['urlsCount'] > 0) {
          $array['url'] .= '_'.$id;
        }
      }
    }

    // перехватываем данные для записи, если выбран другой язык
    $lang = null;
    if (isset($array['lang'])) {
      $lang = $array['lang'];
    }
    unset($array['lang']);


    // фильтрация данных
    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
    foreach ($array as $k => $v) {
      if(in_array($k, $maskField)) {
        $v = htmlspecialchars_decode($v);
        $array[$k] = htmlspecialchars($v);
      }
    }

    $result = false;

    // Если происходит обновление параметров.
    if (!empty($id)) {
      unset($array['delete_image']);

      if(isset($array['weight']) && $array['weight']) {
        $array['weight'] = (double)str_replace(array(',',' '), array('.',''), $array['weight']);
      }

      if(isset($array['price']) && $array['price']) {
        $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
      }
      if(isset($array['price_course']) && $array['price_course']) {
        $array['price_course'] = (double)str_replace(array(',',' '), array('.',''), $array['price_course']);
      }
      if(isset($array['price_course']) && empty($array['price_course'])) {
        unset($array['price_course']);
      }

      if (isset($array['code']) && empty($array['code'])) {
        unset($array['code']);
      }

      // логгер
      $array['id'] = $id;
      $user_log_array = $array;
      $user_log_array['userProperty'] = $userProperty;
      if(isset($variants)){
        if(count($variants)>0) {
          $user_log_array['variants'] = $variants;
        }
      }
      LoggerAction::logAction('Product',__FUNCTION__, $user_log_array);


      unset($array['landingTemplate']);
      unset($array['landingColor']);
      unset($array['ytp']);
      unset($array['landingImage']);
      unset($array['landingSwitch']);

      // фикс для размерной сетки, чтобы сюда не шло то, что не надо
      unset($array['color']);
      unset($array['size']);

      unset($array['storage']);

      foreach ($array as $key => $value) {
        if($key == '') unset($array[$key]);
      }

      if (!empty($userProperty)) {
        $res = DB::query("SELECT `cat_id` FROM `".PREFIX."product` WHERE id = ".DB::quoteInt($id));
        $row = DB::fetchAssoc($res);
        $oldCatId = $row['cat_id'];
        Property::addCategoryBinds($oldCatId, $array['cat_id'], $userProperty);
      }

      $setParts = [];
      foreach ($array as $field => $value) {
        $setParts[] = '`'.$field.'` = '.DB::quote($value);
      }
      // Обновляем стандартные  свойства продукта.
      if (DB::query('
          UPDATE `'.PREFIX.'product`
          SET '.implode(', ', $setParts).'
          WHERE id = '.DB::quote($id))) {


        // Обновляем пользовательские свойства продукта.
        if (!empty($userProperty)) {
          Property::saveUserProperty($userProperty, $id, $lang);
        }


        // Эта проверка нужна только для того, чтобы исключить удаление
        //вариантов при обновлении продуктов не из карточки товара в админке,
        //например по нажатию на "лампочку".
        if (!empty($variants) || $updateFromModal) {

          // обновляем и добавляем варианты продукта.
          if ($variants === null) {
            $variants = array();
          }

          if(!empty($variants[0]['code'])) {
            $arrayVariant = array('code' => $variants[0]['code']);
            $this->fastUpdateProduct($array['id'], $arrayVariant);
          }
          // оключаем сохранение вариантов, когда выбран другой язык, чтобы все не поломать
          if(empty($localeDataVariants)) {
            $this->saveVariants($variants, $id);
          }
        }

        $result = true;
      }
    } else {
      $result = $this->addProduct($array);
    }

    $currencyShopIso = MG::getSetting('currencyShopIso');

    $this->updatePriceCourse($currencyShopIso, array($id));

    Storage::clear('product-'.$id, 'sizeMap-'.$id, 'catalog', 'prop');

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Обновляет поле в варианте и синхронизирует привязку первого варианта с продуктом.
   * <code>
   * $array = array(
   * 'price' => 200, // цена
   * 'count' => 50 // количество
   * );
   * $model = new Models_Product();
   * $model->fastUpdateProductVariant(5, $array, 2);
   * </code>
   * @param int $id id варианта.
   * @param array $array ассоциативный массив поле=>значение.
   * @param int $product_id id продукта.
   * @return bool
   */
  public function fastUpdateProductVariant($id, $array, $product_id) {

    //логирование
    $logArray = $array;
    $logArray['variant_id'] = $id;
    $logArray['id'] = $product_id;
    LoggerAction::logAction('Product',__FUNCTION__, $logArray);

    if (!DB::query('
       UPDATE `'.PREFIX.'product_variant`
       SET '.DB::buildPartQuery($array).'
       WHERE id = '.DB::quote($id))) {
      return false;
    };

    // Следующие действия выполняются для синхронизации  значений первого
    // варианта со значениями записи продукта из таблицы product.
    // Перезаписываем в $array новое значение от первого в списке варианта,
    // и получаем id продукта от этого варианта
    $variants = $this->getVariants($product_id);

    $field = array_keys($array);
    foreach ($variants as $key => $value) {
      $array[$field[0]] = $value[$field[0]];
      break;
    }

    // Обновляем продукт в соответствии с первым вариантом.
    $this->fastUpdateProduct($product_id, $array);
    return true;
  }

  /**
   * Аналогичная fastUpdateProductVariant функция, но с поправками для
   * процесса импорта вариантов.
   * <code>
   *   $model = new Models_Product();
   *   $model->importUpdateProductVariant(5, $array, 2);
   * </code>
   * @param int $id id варианта.
   * @param array $array массив поле = значение.
   * @param int $product_id id продукта.
   * @return bool
   */
  public function importUpdateProductVariant($id, $array, $product_id) {
    if($array['weight']) {
     $array['weight'] = (double)str_replace(array(',',' '), array('.',''), $array['weight']);
    }

    if($array['price']) {
      $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
    }

    if(isset($array['price_course']) && $array['price_course']) {
      $array['price_course'] = (double)str_replace(array(',',' '), array('.',''), $array['price_course']);
    }
    
    if(empty($array['price_course'])) {
      unset($array['price_course']);
    }

   // костыль, на будущее, может пригодится, от нулевых price_course
   // if(empty($array['price_course'])|| $array['price_course']===0) {
   //   $array['price_course']=$array['price'];
   // }

    if (!$id || !DB::query('
       UPDATE `'.PREFIX.'product_variant`
       SET '.DB::buildPartQuery($array).'
       WHERE id = %d
     ', $id)) {
      $res = DB::query('SELECT MAX(id) FROM '.PREFIX.'product_variant');
      while($row = DB::fetchAssoc($res)) {
        $array['sort'] = $row['MAX(id)']+1;
      }
      DB::query('
       INSERT INTO `'.PREFIX.'product_variant`
       SET '.DB::buildPartQuery($array)
      );
    };

    return true;
  }

  /**
   * Обновление заданного поля продукта.
   * <code>
   * $array = array(
   * 'price' => 200, // цена
   * 'sort' => 5, // номер сортировки
   * 'count' => 50 // количество
   * );
   * $model = new Models_Product();
   * $model->fastUpdateProduct(5, $array);
   * </code>
   * @param int $id - id продукта.
   * @param array $array - параметры для обновления.
   * @return bool
   */
  public function fastUpdateProduct($id, $array) {
    if(isset($array['price']) && $array['price']) {
      $array['price'] = (double)str_replace(array(',',' '), array('.',''), $array['price']);
    }
    if(isset($array['sort']) && $array['sort']) {
      $array['sort'] = (int)str_replace(array(',',' '), array('.',''), $array['sort']);
    }
    if(isset($array['count']) && $array['count']) {
      $array['count'] = (float)str_replace(array(',',' '), array('.',''), $array['count']);
      $array['count'] = round($array['count'],2);
    }

    //логирование
    $logArray = $array;
    $logArray['id'] = $id;
    LoggerAction::logAction('Product',__FUNCTION__, $logArray);

    $setParts = [];
    foreach ($array as $field => $value) {
      $setParts[] = '`'.$field.'` = '.DB::quote($value);
    }

    if (!DB::query('
      UPDATE `'.PREFIX.'product`
      SET '.implode(', ', $setParts).'
      WHERE id = %d
    ', $id)) {
      return false;
    };

    if(isset($array['price'])) {
      $currencyShopIso = MG::getSetting('currencyShopIso');
      $this->updatePriceCourse($currencyShopIso, array($id));
    }

    $result = true;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Сохраняет варианты товара.
   * <code>
   * $variants = Array(
   *  0 => Array(
   *     'color' => 19, // id цвета варианта
   *     'size' => 11, // id размера варианта
   *     'title_variant' => '22 Голубой', // название
   *     'code' => 'SKU241', // артикул
   *     'price' => 2599, // цена
   *     'old_price' => 3000, // старая цена
   *     'weight' => 1, // вес
   *     'count' => 50, // количество
   *     'activity' => 1, // активность
   *     'id' => 1249, // id варианта
   *     'currency_iso' => 'RUR', // код валюты
   *     'image' => '13140250299.jpg' // название картинки варианта
   *  )
   * );
   * $model = new Models_Product();
   * $model->saveVariants($variants, 51);
   * </code>
   * @param array $variants набор вариантов
   * @param int $id id товара
   * @return bool
   */
  public function saveVariants($variants = array(), $id) {
    $existsVariant = $countArray = array();
    $count = 0;

    $dbRes = DB::query('SHOW COLUMNS FROM `'.PREFIX.'product` WHERE FIELD = \'system_set\'');
    if(!$row = DB::fetchArray($dbRes)) {
      return false;
    }

    $dbRes = DB::query("SELECT * FROM `".PREFIX."product_variant` WHERE product_id = ".DB::quote($id));

    while ($arRes = DB::fetchAssoc($dbRes)) {
      $existsVariant[$arRes['id']] = $arRes;
    }

    foreach ($variants as $item) {
      if (!isset($item['id'])) {continue;}
      $res = DB::query('SELECT count FROM '.PREFIX.'product_variant WHERE id = '.DB::quoteInt($item['id']));
      while ($row = DB::fetchAssoc($res)) {
        $countArray[$item['id']] = $row['count'];
      }
    }

    // Удаляем все имеющиеся товары.
    $res = DB::query("DELETE FROM `".PREFIX."product_variant` WHERE product_id = ".DB::quote($id));


    // Если вариантов как минимум два.
   // if (count($variants) > 1) {
      // Сохраняем все отредактированные варианты.
    $i = 1;
    $recalculateStorage = false;
    foreach ($variants as $variant) {
      if (isset($variant['id']) && !empty($existsVariant[$variant['id']]['1c_id'])) {
        $variant['1c_id'] = $existsVariant[$variant['id']]['1c_id'];
      }
      if (empty($variant['code'])) {
        $variant['code'] = MG::getSetting('prefixCode').$id.'_'.$i;
      }
      $variant['sort'] = $i++;
      unset($variant['product_id']);
      unset($variant['rate']);
      unset($variant['count_sort']);
      unset($variant['weightCalc']);
      if(!empty($variant['id'])) {

      }

      $varId = isset($variant['id'])?$variant['id']:null;
      if(isset($this->clone) && $this->clone) {
        unset($variant['id']);
      }
      DB::query(' 
        INSERT  INTO `'.PREFIX.'product_variant` 
        SET product_id= '.DB::quote($id).", ".DB::buildPartQuery($variant)
      );

      $newVarId = DB::insertId();

      if($i === 2 && MG::get('wholesales_prices') != 0){
        //если вариантов не было, передаем оптовые цены товара первому варианту 
        if (count($variants) > 1 && empty($existsVariant)){

          $matchProductToVariant = '
            UPDATE 
              '.PREFIX.'wholesales_sys 
            SET 
              variant_id = '.DB::quoteInt($newVarId).'
            WHERE 
              product_id = '.DB::quoteInt($id).' AND
              variant_id = 0';

          DB::query($matchProductToVariant);
        }

        if (count($variants) <= 1 && !empty($existsVariant)){

          $variantToProduct = '
          UPDATE 
            '.PREFIX.'wholesales_sys 
          SET 
            `variant_id` = 0
          WHERE 
            `variant_id` = '.DB::quoteInt($newVarId);

          if(!empty($matchProductToVariant)){
            DB::query($matchProductToVariant);
          }

          $deleteVariantsFromWholeTable = '
          DELETE FROM 
            '.PREFIX.'wholesales_sys 
          WHERE 
            `product_id` = '.DB::quoteInt($id).' AND
            `variant_id` != 0';

          DB::query($deleteVariantsFromWholeTable);
        }
      }

    }
    if ($recalculateStorage) {
      $this->recalculateStoragesById($id);
    }
   // }
  }

  /**
   * Клонирует товар.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $model->cloneProduct($productId);
   * </code>
   * @param int $id id клонируемого товара.
   * @return array
   */
  public function cloneProduct($id) {
    $result = false;

    $arr = $this->getProduct($id, true, true);
    $arr['unit'] = $arr['product_unit'];
    $arr['title'] = htmlspecialchars_decode($arr['title']);
    $image_url = basename($arr['image_url']);

    foreach ($arr['images_product'] as $k=>$image) {
      $arr['images_product'][$k] = basename($image);
    }
    $arr['image_url'] = implode("|", $arr['images_product']);
    $imagesArray = $arr['images_product'];

    $userProperty = $arr['thisUserFields'];

    unset($arr['product_unit']);
    unset($arr['category_unit']);
    unset($arr['product_weightUnit']);
    unset($arr['category_weightUnit']);
    unset($arr['weightUnit']);
    unset($arr['weightCalc']);
    unset($arr['count_hr']);
    unset($arr['real_category_unit']);
    unset($arr['category_name']);
    unset($arr['thisUserFields']);
    unset($arr['category_url']);
    unset($arr['product_url']);
    unset($arr['images_product']);
    unset($arr['images_title']);
    unset($arr['images_alt']);
    unset($arr['rate']);
    unset($arr['plugin_message']);
    unset($arr['id']);
    unset($arr['count_buy']);
    $arr['code'] = '';
    $arr['userProperty'] = $userProperty;
    $variants = $this->getVariants($id);

    foreach ($variants as &$item) {
      // unset($item['id']);
      unset($item['product_id']);
      unset($item['rate']);
      $item['code'] = '';
      $imagesArray[] = $item['image'];
    }

    $arr['variants'] = $variants;

    // перед клонированием создадим копии изображений,
    // чтобы в будущем можно было без проблемно удалять их вместе с удалением продукта
    $result = $this->addProduct($arr, true);

    $this->cloneImagesProduct($imagesArray, $id, $result['id']);



    $result['image_url'] = $image_url;
    $result['currency'] = MG::getSetting('currency');

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

   /**
     * Клонирует изображения продукта.
     * <code>
     *   $imagesArray = array(
     *     '40Untitled-1.jpg',
     *     '41Untitled-1.jpg',
     *     '42Untitled-1.jpg'
     *   );
     *   $oldId = 40;
     *   $newId = 130;
     *   $model = new Models_Product;
     *   $model->deleteProduct($imagesArray, $oldId, $newId);
     * </code>
     * @param array $imagesArray массив url изображений, которые надо клонировать.
     * @param int $oldId старый ID товара.
     * @param int $newId новый ID товара.
     * @return bool
     */
  public function cloneImagesProduct($imagesArray = array(), $oldId = 0, $newId = 0) {
    if(!$oldId && !$newId) return false;
    $ds = DS;
    $documentroot = str_replace($ds.'mg-core'.$ds.'models','',dirname(__FILE__)).$ds;
    $dir = floor($oldId/100).'00'.$ds.$oldId;
    $this->movingProductImage($imagesArray, $newId, 'uploads'.$ds.'product'.$ds.$dir, false);

    return true;
  }

  /**
   * Удаляет товар, его свойства, варианты, локализации, оптовые цены из базы данных.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $model->deleteProduct($productId);
   * </code>
   * @param int $id id удаляемого товара
   * @return bool
   */
  public function deleteProduct($id) {
    $result = false;
    $prodInfo = $this->getProduct($id);

    // $this->deleteImagesProduct($prodInfo['images_product'], $id);
    // $this->deleteImagesVariant($id);
    // $this->deleteImagesFolder($id);
    $imgFolder = SITE_DIR.'uploads'.DS.'product'.DS.floor($id/100).'00'.DS.$id;
    $imgWebpFolder = SITE_DIR.'uploads'.DS.'webp'.DS.'product'.DS.floor($id/100).'00'.DS.$id;
    MG::rrmdir($imgFolder);
    MG::rrmdir($imgWebpFolder);


	// логгер
    LoggerAction::logAction('Product',__FUNCTION__, $id);

    // Удаляем продукт из базы.
    DB::query('
      DELETE
      FROM `'.PREFIX.'product`
      WHERE id = %d
    ', $id);

    DB::query('
      DELETE
      FROM `'.PREFIX.'product_user_property_data`
      WHERE product_id = %d
    ', $id);

    // Удаляем все варианты данного продукта.
    DB::query('
      DELETE
      FROM `'.PREFIX.'product_variant`
      WHERE product_id = %d
    ', $id);


    $result = true;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

   /**
   * Удаляет папки из структуры папок изображений относящиеся к заданному продукту.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $model->deleteImagesFolder($productId);
   * </code>
   * @param int $id id товара.
   */
  public function deleteImagesFolder($id) {
    if(!empty($id)) {
      $ds = DS;
      $path = 'uploads'.$ds.'product'.$ds.floor($id/100).'00'.$ds.$id;
      if(file_exists($path)) {
        if(file_exists($path.$ds.'thumbs')) {
          rmdir($path.$ds.'thumbs');
        }
        rmdir($path);
      }
    }
  }
  /**
   * Удаляет все картинки привязанные к продукту.
   * <code>
   *   $array = array(
   *    'product/100/105/120.jpg',
   *    'product/100/105/122.jpg',
   *    'product/100/105/121.jpg'
   *  );
   *  $model = new Models_Product();
   *  $model->deleteImagesProduct($array);
   * </code>
   * @param array $arrayImages массив с названиями картинок
   * @param int $productId ID товара
   */
   public function deleteImagesProduct($arrayImages = array(), $productId = false) {
     if(empty($arrayImages)) {
       return true;
     }
     // удаление картинки с сервера
    $uploader = new Upload(false);
    foreach ($arrayImages as $key => $imageName) {
      $pos = strpos($imageName, 'no-img');
      if(!$pos && $pos !== 0) {
        $uploader->deleteImageProduct($imageName, $productId);
      }
    }
  }
  /**
   * Получает информацию о запрашиваемом товаре.
   * <code>
   * $where = '`cat_id` IN (5,6)';
   * $model = new Models_Product;
   * $result = $model->deleteImagesFolder($where);
   * viewData($result);
   * </code>
   * @param string $where необязательный параметр, формирующий условия поиска, например: id = 1
   * @return array массив товаров
   */
  public function getProductByUserFilter($where = '', $joinVariant = false) {
    $result = array();

    if ($where) {
      $where = ' WHERE '.$where;
    }

    if ($joinVariant) {
      $res = DB::query('
      SELECT  CONCAT(c.parent_url,c.url) as category_url,
        p.url as product_url, p.*, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`,
        p.`currency_iso`
      FROM `'.PREFIX.'product` p
        LEFT JOIN `'.PREFIX.'category` c
        ON c.id = p.cat_id
        LEFT JOIN `'.PREFIX.'product_variant` pv 
        ON pv.product_id = p.id
      '.$where);
    } else {
      $res = DB::query('
      SELECT  CONCAT(c.parent_url,c.url) as category_url,
        p.url as product_url, p.*, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
        p.`currency_iso`
      FROM `'.PREFIX.'product` p
        LEFT JOIN `'.PREFIX.'category` c
        ON c.id = p.cat_id
      '.$where);
    }



    while ($product = DB::fetchAssoc($res)) {
      $result[$product['id']] = $product;
    }
    if (!empty($result) && $joinVariant) {
      $catalogModel = new Models_Catalog();
      $result = $catalogModel->addPropertyToProduct($result);
      foreach ($result as $productId => $product) {
        $result[$productId]['variant_exist'] = $product['variants'][0]['id'];
      }
    }
    return $result;
  }

  /**
   * Получает информацию о запрашиваемом товаре по его ID.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $product = $model->getProduct($productId);
   * viewData($product);
   * </code>
   * @param int $id id запрашиваемого товара.
   * @param bool $getProps возвращать ли характеристики.
   * @param bool $disableCashe отключить ли кэш.
   * @return array массив с данными о товаре.
   */
  public function getProduct($id, $getProps = true, $disableCashe = false) {
    $prodCash = false;
    if(!$disableCashe && $getProps) $prodCash = Storage::get('product-'.$id.'-'.LANG.'-'.MG::getSetting('currencyShopIso'));

    if(!$prodCash) {
      $id =  intval($id);
      $result = array();
      $res = DB::query('
        SELECT  CONCAT(c.parent_url,c.url) as category_url, c.title as category_name,
          c.unit as category_unit, p.unit as product_unit,
          c.weight_unit as category_weightUnit, p.weight_unit as product_weightUnit,
          p.url as product_url, p.*, rate, (p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
          p.`currency_iso` 
        FROM `'.PREFIX.'product` p
          LEFT JOIN `'.PREFIX.'category` c
          ON c.id = p.cat_id
        WHERE p.id = '.DB::quoteInt($id, true));

      if (!empty($res)) {
        if ($product = DB::fetchAssoc($res)) {
          $result = $product;



          $imagesConctructions = $this->imagesConctruction($result['image_url'],$result['image_title'],$result['image_alt'], $result['id']);
          $result['images_product'] = $imagesConctructions['images_product'];
          $result['images_title'] = $imagesConctructions['images_title'];
          $result['images_alt'] = $imagesConctructions['images_alt'];
          $result['image_url'] = $imagesConctructions['image_url'];
          $result['image_title'] = $imagesConctructions['image_title'];
          $result['image_alt'] = $imagesConctructions['image_alt'];


          $result['unit'] = $result['product_unit'];
        }
      }
      if (empty($result['id'])) {
        return null;
      }

      if (!isset($result['category_unit'])) {
        $result['category_unit'] = 'шт.';
      }

      $cat = [
        'title' => $result['category_name'],
        'unit'=>$result['category_unit'],
      ];
      MG::loadLocaleData($id, LANG, 'product', $result);
      MG::loadLocaleData($result['cat_id'], LANG, 'category', $cat);
      if ($cat['title']) {
        $result['category_name'] = $cat['title'];
      }
      $result['product_unit'] = !empty($result['unit'])?$result['unit']:'';
      $result['real_category_unit'] = $result['category_unit'];
      $result['real_category_unit'] = $cat['unit'];
      if (isset($result['product_unit']) && $result['product_unit'] != null && strlen($result['product_unit']) > 0) {
        $result['category_unit'] = $result['product_unit'];
      }
      if (!empty($result['product_weightUnit'])) {
        $result['weightUnit'] = $result['product_weightUnit'];
      } elseif(!empty($result['category_weightUnit'])) {
        $result['weightUnit'] = $result['category_weightUnit'];
      } else {
        $result['weightUnit'] = 'kg';
      }
      if ($result['weightUnit'] != 'kg') {
        $result['weightCalc'] = MG::getWeightUnit('convert', ['from'=>'kg','to'=>$result['weightUnit'],'value'=>$result['weight']]);
      } else {
        $result['weightCalc'] = $result['weight'];
      }

      if ($getProps) {
        Storage::save('product-'.$id.'-'.LANG.'-'.MG::getSetting('currencyShopIso'), $result);
      }
    } else {
      $result = $prodCash;

      if(MG::enabledStorage()) {
      } else {
        $res = DB::query('SELECT `count`
          FROM '.PREFIX.'product
          WHERE `id` = '.DB::quoteInt($id));
        while ($row = DB::fetchAssoc($res)) {
          $result['count'] = $row['count'];
        }
      }
    }

    //В обход кэша меняем название количества
    $result['count_hr'] = '';
    $convertCountToHR = MG::getSetting('convertCountToHR');
    if (!empty($convertCountToHR) && !MG::isAdmin()) {
      $result['count_hr'] = MG::convertCountToHR($result['count']);
    }

    // подгрузка цен без кэша
    if(!MG::isAdmin()) {
      $res = DB::query('SELECT p.id, p.price, p.price_course * (IFNULL(c.rate, 0) + 1) AS price_course FROM '.PREFIX.'product AS p
        LEFT JOIN '.PREFIX.'category AS c ON c.id = p.cat_id
        WHERE p.id = '.DB::quoteInt($id));
      while($row = DB::fetchAssoc($res)) {
        $result['price'] = MG::convertPrice($row['price']);
        $result['price_course'] = MG::convertPrice($row['price_course']);
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Создает массивы данных для картинок товара, возвращает три массива со ссылками, заголовками и альт, текстами.
   * <code>
   *   $model = new Models_Product();
   *   $imageUrl = '120.jpg|121.jpg';
   *   $imageTitle = 'Каритинка товара';
   *   $imageAlt = 'Альтернативная подпись картинки';
   *   $res = $model->imagesConctruction($imageUrl, $imageTitle, $imageAlt);
   *   viewData($res);
   * </code>
   * @param string $imageUrl строка с разделителями | между ссылок.
   * @param string $imageTitle строка с разделителями | между заголовков.
   * @param string $imageAlt строка с разделителями | между тестов.
   * @param string $id ID товара.
   * @return array
   */
  public function imagesConctruction($imageUrl, $imageTitle, $imageAlt, $id = 0) {
    $result = array(
      'images_product'=>array(),
      'images_title'=>array(),
      'images_alt'=>array()
    );

    // Получаем массив картинок для продукта, при этом первую в наборе делаем основной.
    $arrayImages = explode("|", $imageUrl);

    foreach($arrayImages as $cell=>$image) {
      $arrayImages[$cell] = str_replace(SITE.'/uploads/', '', mgImageProductPath($image, $id));
    }

    if (!empty($arrayImages)) {
      $result['image_url'] = $arrayImages[0];
    }

    $result['images_product'] = $arrayImages;
    // Получаем массив title для картинок продукта, при этом первый в наборе делаем основной.
    $arrayTitles = explode("|", $imageTitle);
    if (!empty($arrayTitles)) {
      $result['image_title'] = $arrayTitles[0];
    }

    $result['images_title'] = $arrayTitles;

    // Получаем массив alt для картинок продукта, при этом первый в наборе делаем основной.
    $arrayAlt = explode("|", $imageAlt);
    if (!empty($arrayAlt)) {
      $result['image_alt'] = $arrayAlt[0];
    }

    $result['images_alt'] = $arrayAlt;

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Обновляет остатки продукта, увеличивая их на заданное количество.
   * <code>
   * Models_Product::increaseCountProduct(37, 'SKU348', 2);
   * </code>
   * @param int $id номер продукта.
   * @param string $code артикул.
   * @param int $count прибавляемое значение к остатку.
   */
  public function increaseCountProduct($id, $code, $count) {

    $sql = "
      UPDATE `".PREFIX."product_variant` as pv 
      SET pv.`count`= pv.`count`+".DB::quoteFloat($count)." 
      WHERE pv.`product_id`=".DB::quoteInt($id)." 
        AND pv.`code`=".DB::quote($code)." 
        AND pv.`count`>=0
    ";

    DB::query($sql);

    $sql = "
      UPDATE `".PREFIX."product` as p 
      SET p.`count`= p.`count`+".DB::quoteFloat($count)." 
      WHERE p.`id`=".DB::quoteInt($id)." 
        AND p.`code`=".DB::quote($code)." 
        AND  p.`count`>=0
    ";

    DB::query($sql);
  }

  /**
   * Обновляет остатки продукта, уменьшая их количество,
   * при смене статуса заказа с "отменен" на любой другой.
   * <code>
   * Models_Product::decreaseCountProduct(37, 'SKU348', 2);
   * </code>
   * @param int $id ID продукта.
   * @param string $code Артикул.
   * @param int $count Прибавляемое значение к остатку.
   */
  public function decreaseCountProduct($id, $code, $count) {

    $product = $this->getProduct($id);
    $variants = $this->getVariants($product['id']);
    foreach ($variants as $idVar => $variant) {
      if ($variant['code'] == $code) {
        $variantCount = ($variant['count'] * 1 - $count * 1) >= 0 ? $variant['count'] - $count : 0;
        $sql = "
          UPDATE `".PREFIX."product_variant` as pv 
          SET pv.`count`= ".DB::quoteFloat($variantCount, true)." 
          WHERE pv.`id`=".DB::quoteInt($idVar)." 
            AND pv.`code`=".DB::quote($code)." 
            AND  pv.`count`>0";
        DB::query($sql);
      }
    }

    $product['count'] = ($product['count'] * 1 - $count * 1) >= 0 ? $product['count'] - $count : 0;
    $sql = "
      UPDATE `".PREFIX."product` as p 
      SET p.`count`= ".DB::quoteFloat($product['count'], true)." 
      WHERE p.`id`=".DB::quoteInt($id)." 
        AND p.`code`=".DB::quote($code)."
        AND  p.`count`>0";
    DB::query($sql);
  }

  /**
   * Удаляет все миниатюры и оригинал изображения товара из папки upload.
   * @param array $arrayDelImages массив с изображениями для удаления
   * @return bool
   * @deprecated
   */
  public function deleteImageProduct($arrayDelImages) {
    if (!empty($arrayDelImages)) {
      foreach ($arrayDelImages as $value) {
        if (!empty($value)) {
          // Удаление картинки с сервера.
          if (is_file(SITE_DIR."uploads/".basename($value))) {
            unlink(SITE_DIR."uploads/".basename($value));
            if (is_file(SITE_DIR."uploads/thumbs/30_".basename($value))) {
              unlink(SITE_DIR."uploads/thumbs/30_".basename($value));
            }
            if (is_file(SITE_DIR."uploads/thumbs/70_".basename($value))) {
              unlink(SITE_DIR."uploads/thumbs/70_".basename($value));
            }
          }
        }
      }
    }
    return true;
  }

  /**
   * Возвращает общее количество продуктов каталога.
   * <code>
   * $result = Models_Product::getProductsCount();
   * viewData($result);
   * </code>
   * @return int количество товаров.
   */
  public function getProductsCount($where = '') {
    if ($where) {
      $where = 'WHERE '.$where;
    }

    $result = 0;
    $res = DB::query('
      SELECT count(id) as count
      FROM `'.PREFIX.'product`
    '.$where);

    if ($product = DB::fetchAssoc($res)) {
      $result = $product['count'];
    }

    return $result;
  }

  /**
   * Получает продукт по его URL.
   * <code>
   * $url = 'nike-air-versitile_102';
   * $result = Models_Product::getProductByUrl($url);
   * viewData($result);
   * </code>
   * @param string $url запрашиваемого товара.
   * @param int $catId id-категории, т.к. в разных категориях могут быть одинаковые url.
   * @return array массив с данными о товаре.
   */
  public function getProductByUrl($url, $catId = false) {
    $result = array();
    $where = '';
    if ($catId !== false) {
      $where = ' and cat_id='.DB::quote($catId);
    }

    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'product`
      WHERE url = '.DB::quote($url).' 
    '.$where);

    if (!empty($res)) {
      if ($product = DB::fetchAssoc($res)) {
        $result = $product;
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает цену запрашиваемого товара по его id.
   * <code>
   * $result = Models_Product::getProductPrice(5);
   * viewData($result);
   * </code>
   * @param int $id id изменяемого товара.
   * @return bool|float $error в случаи ошибочного запроса.
   */
  public function getProductPrice($id) {
    $result = false;
    $res = DB::query('
      SELECT price
      FROM `'.PREFIX.'product`
      WHERE id = %d
    ', $id);

    if ($row = DB::fetchObject($res)) {
      $result = $row->price;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Создает форму пользовательских характеристик для товара.
   * В качестве входящего параметра получает массив:
   * <code>
   * $param = array(
   *   'id' => null, // id товара.
   *   'maxCount' => null, // максимальное количество товара на складе.
   *   'productUserFields' => null, // массив пользовательских полей для данного продукта.
   *   'action' => "/catalog", // ссылка для метода формы.
   *   'method' => "POST", // тип отправки данных на сервер.
   *   'ajax' => true, // использовать ajax для пересчета стоимости товаров.
   *   'blockedProp' => array(), // массив из ID свойств, которые не нужно выводить в форме.
   *   'noneAmount' => false, // не выводить  input для количества.
   *   'titleBtn' => "В корзину", // название кнопки.
   *   'blockVariants' => '', // блок вариантов.
   *   'classForButton' => 'addToCart buy-product buy', // классы для кнопки.
   *   'noneButton' => false, // не выводить кнопку отправки.
   *   'addHtml' => '' // добавить HTML в содержимое формы.
   *   'currency_iso' => '', // обозначение валюты в которой сохранен товар
   *   'printStrProp' => 'true', // выводить строковые характеристики
   *   'printCompareButton' => 'true', // выводить кнопку сравнения
   *   'buyButton' => 'true', // показывать кнопку 'купить' в миникарточках (если false - показывается кнопка 'подробнее')
   *   'productData' => 'Array', // массив с данными о товаре
   *   'showCount' => 'true' // показывать блок с количеством
   * );
   * $model = new Models_Product;
   * $result = $model->getProduct($param);
   * echo $result;
   * </code>
   * @param array $param массив параметров.
   * @param string $adminOrder заказ для админки или нет (по умолчанию - нет).
   * @return string html форма.
   */
  public function createPropertyForm(
    $param = array(
      'id' => null,
      'maxCount' => null,
      'productUserFields' => null,
      'action' => "/catalog",
      'method' => "POST",
      'ajax' => true,
      'blockedProp' => array(),
      'noneAmount' => false,
      'titleBtn' => "В корзину",
      'blockVariants' => '',
      'classForButton' => 'addToCart buy-product buy',
      'noneButton' => false,
      'addHtml' => '',
      'printStrProp' => null,
      'printCompareButton' => null,
      'buyButton' => '',
      'currency_iso' => '',
      'productData' => null,
      'showCount' => true,
    ),
    $adminOrder = 'nope',
    $returnArray = false
  ) {
    extract($param);

    $productCurr = !empty($param['productData']['currency_iso'])?$param['productData']['currency_iso']:null;

    if (empty($classForButton)) {
      $classForButton = 'addToCart buy-product buy';
    }
    if ((!isset($id) || $id === null) || (!isset($maxCount) || $maxCount === null)) {
      return "error param!";
    }
    if (empty($printStrProp)) {
      $printStrProp = MG::getSetting('printStrProp');
    }
    if (!isset($printCompareButton) || $printCompareButton===null) {
      $printCompareButton = MG::getSetting('printCompareButton');
    }

    if(!isset($this->groupProperty)){
      $this->groupProperty = Property::getPropertyGroup(true);
    }

    $catalogAction = MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView';
    // если используется аяксовый метод выбора, то подключаем доп класс для работы с формой.
    $marginPrice = 0; // добавочная цена, в зависимости от выбранных автоматом характеристик
    $secctionCartNoDummy = array(); //Не подставной массив характеристик, все характеристики с настоящими #ценами#
    //в сессию записать реальные значения, в паблик подмену, с привязкой в конце #№
    $html = '';
   //if ($ajax) {
    //  mgAddMeta("<script type=\"text/javascript\" src=\"".SITE."/mg-core/script/jquery.form.js\"></script>");
    //}

    $currencyRate = MG::getSetting('currencyRate');
    $currencyShort = MG::getSetting('currencyShort');
    $currency_iso = MG::getSetting('currencyShopIso');
    $currencyRate = $currencyRate[$currency_iso];
    $currencyShort = $currencyShort[$currency_iso];
    $propPieces = array();
    $htmlProperty = '';


    if (!isset($noneButton)) {$noneButton = null;}
    if (!isset($buyButton)) {$buyButton = null;}
    if (!isset($addHtml)) {$addHtml = null;}
    if (!isset($noneAmount)) {$noneAmount = false;}
    if (!isset($ajax)) {$ajax = true;}
    if (!isset($titleBtn)) {$titleBtn = 'В корзину';}
    if (!isset($blockVariants)) {$blockVariants = '';}
    if (!isset($showCount)) {$showCount = true;}
    if (!isset($action)) {$action = '/catalog';}
    if (!isset($method)) {$method = 'POST';}

    if (!isset($stringsProperties)) {
      $stringsProperties = array();
    } else {
      uasort($stringsProperties, function ($a, $b) {
        if (isset($a['0']['group_prop']['sort'])) {
          $tmpA = $a['0']['group_prop']['sort'];
        } else {
          $tmpA = '';
        }
        if (isset($b['0']['group_prop']['sort'])) {
          $tmpB = $b['0']['group_prop']['sort'];
        } else {
          $tmpB = '';
        }
        return strcmp($tmpA, $tmpB);
      });
    }
    if (!isset($filesProperties)) {
      $filesProperties = array();
    } else {
      uasort($filesProperties, function ($a, $b) {
        if (isset($a['0']['group_prop']['sort'])) {
          $tmpA = $a['0']['group_prop']['sort'];
        } else {
          $tmpA = '';
        }
        if (isset($b['0']['group_prop']['sort'])) {
          $tmpB = $b['0']['group_prop']['sort'];
        } else {
          $tmpB = '';
        }
        return strcmp($tmpA, $tmpB);
      });
    }
    if (!isset($defaultSet)) {$defaultSet = array();}

    if (!isset($productData)) {
      $productData['price_course'] = '';
      $productData['old_price'] = '';
      $productData['activity'] = null;
    }

    $data = array(
     'maxCount' => $maxCount,
     'noneAmount' => $noneAmount,
     'noneButton' => $noneButton,
     'printCompareButton' => $printCompareButton,
     'ajax' => $ajax,
     'buyButton' => $buyButton,
     'classForButton' => $classForButton,
     'titleBtn' => $titleBtn,
     'id' => $id,
     'blockVariants' => $blockVariants,
     'addHtml' => $addHtml,
     'price' => $productData['price_course'],
     'old_price' => $productData['old_price'],
     'activity' => $productData['activity'],
     'parentData' => $param,
     'htmlProperty' => $htmlProperty,
     'showCount' => $showCount,
     'action' => $action,
     'method' => $method,
     'catalogAction' => $catalogAction,
    );


    if (!$returnArray) {
      if ($adminOrder == 'yep') {
        $htmlLayout = MG::adminLayout('adminOrder.php', $data);
      } else {
        $htmlLayout = MG::layoutManager('layout_property', $data);
      }
      if (strpos($htmlLayout, '<form') === false ||
          strpos($htmlLayout, $action) === false ||
          strpos($htmlLayout, $method) === false ||
          strpos($htmlLayout, $catalogAction) === false ||
          strpos($htmlLayout, '</form>') === false
          ) {
        $htmlForm = '<form action="'.SITE.$action.'" method="'.$method.'" class="property-form '.$catalogAction.'" data-product-id='.$id.'>';
        $htmlForm .= $htmlLayout;
        $htmlForm .= '</form>';
      }
      else{
        $htmlForm = $htmlLayout;
      }

      $result = array(
        'html' => $htmlForm,
        'marginPrice' => $marginPrice * $currencyRate,
        'defaultSet' => $defaultSet,  // набор характеристик, которые были бы выбраны по умолчанию при открытии карточки товара.
        'propertyNodummy' => $secctionCartNoDummy,
        'stringsProperties' => $stringsProperties,
        'filesProperties' => $filesProperties,
      );
    } else {
      unset($data['parentData']);
      unset($data['blockVariants']);
      $result = array(
        'propertyData' => $data,
        'marginPrice' => $marginPrice * $currencyRate,
        'defaultSet' => $defaultSet,
        'propertyNodummy' => $secctionCartNoDummy,
        'stringsProperties' => $stringsProperties,
        'filesProperties' => $filesProperties,
      );
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Формирует блок вариантов товара.
   * <code>
   * $model = new Models_Product;
   * $result = $model->getBlockVariants(5);
   * echo $result;
   * </code>
   * @param int $id id товара
   * @param int $cat_id id категории
   * @param string $returnArray вернуть массив (по умолчанию - нет)
   * @return string|array (array - для админки)
   */
  public function getBlockVariants($id, $cat_id = 0, $returnArray = false) {
    $arr = $this->getVariants($id, false, true);

    foreach ($arr as $key => $value) {
      if($value['count'] == 0) {
        $tmp = $value;
        unset($arr[$key]);
        $arr[$tmp['id']] = $tmp;
      }
    }

    $convertCountToHR = MG::getSetting('convertCountToHR');
    foreach ($arr as &$var) {
      $var['count_hr'] = '';
      if (!empty($convertCountToHR) && !MG::isAdmin()) {
        $var['count_hr'] = MG::convertCountToHR($var['count'] );
      }
      $var['price'] = MG::priceCourse($var['price_course']);
    }
    if ($returnArray == 'yep') {
      $html = $arr;
    }
    else{
      $html = MG::layoutManager('layout_variant', array('blockVariants'=>$arr, 'type'=>'product'));
    }
    return $html;
  }

  /**
   * Формирует массив блоков вариантов товаров на странице каталога.
   * Метод создан для сокращения количества запросов к БД.
   * <code>
   * $model = new Models_Product;
   * $result = $model->getBlocksVariantsToCatalog(array(2,3,4));
   * echo $result;
   * </code>
   * @param int $array массив id товаров
   * @param array $returnArray если true то вернет просто массив без html блоков
   * @param bool $mgadmin если true то вернет данные для админки
   * @return string|array
   */
  public function getBlocksVariantsToCatalog($array, $returnArray = false, $mgadmin = false) {
    $results = array();
    $in = '';
    if (!empty($array)) {
      $in = implode(',', $array);
    }
    $orderBy = 'ORDER BY sort, id';
    $where = '';
    if(MG::getSetting('filterSortVariant') && !$mgadmin) {
      $parts = explode('|',MG::getSetting('filterSortVariant'));
      $parts[0] = $parts[0] == 'count' ? 'count_sort' : $parts[0];
      $orderBy = ' ORDER BY `'.DB::quote($parts[0],1).'` '.DB::quote($parts[1],1).', id';
    }
    if(MG::getSetting('showVariantNull')=='false' && !$mgadmin) {
      if(MG::enabledStorage()) {
        $orderBy = ' AND (SELECT round(SUM(ABS(count)),2) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id AND variant_id = pv.id) > 0 '.$orderBy;
      } else {
        $orderBy = ' AND (pv.`count` != 0 OR pv.`count` IS NULL) '.$orderBy;
      }
    }
    $storageCheck = '';
    if(MG::enabledStorage()) {
      $storageCheck = ',(SELECT round(SUM(ABS(count)),2) FROM '.PREFIX.'product_on_storage WHERE product_id = p.id AND variant_id = pv.id) AS count';
    }
    // Получаем все варианты для передранного массива продуктов.
    if ($in) {
      $res = DB::query('
       SELECT pv.*, c.rate,(pv.price_course + pv.price_course * (IFNULL(c.rate,0))) as `price_course`,
       IF( pv.count<0,  1000000, round(pv.count,2) ) AS  `count_sort`
       '.$storageCheck.'
       FROM `'.PREFIX.'product_variant` pv    
         LEFT JOIN `'.PREFIX.'product` as p ON 
           p.id = pv.product_id
         LEFT JOIN `'.PREFIX.'category` as c ON 
           c.id = p.cat_id  
       WHERE pv.product_id  in ('.$in.')
       '.$orderBy);

      if (!empty($res)) {
        while ($variant = DB::fetchAssoc($res)) {
          if (!$returnArray) {

            $variant['price'] = MG::priceCourse($variant['price_course']);
          }
          $results[$variant['product_id']][] = $variant;
        }
      }
    }
    $productCount = 0;

    if(!$mgadmin) {
      foreach ($results as &$blockVariants) {
        for($i = 0; $i < count($blockVariants); $i++) {
          $productCount += $blockVariants[$i]['count'];
          if($blockVariants[$i]['count'] == 0) {
            $blockVariants[] = $blockVariants[$i];
            unset($blockVariants[$i]);
          }
        }
        $blockVariants = array_values($blockVariants);
      }
    }

    if ($returnArray) {
      return $results;
    }

    sort($array);

    $cash = Storage::get('getBlocksVariantsToCatalog-'.md5(json_encode($array).$productCount.LANG));
    if(!$cash) {
      if (!empty($results)) {
        // Для каждого продукта создаем HTML верстку вариантов.
        foreach ($results as &$blockVariants) {
          $html = MG::layoutManager('layout_variant', array('blockVariants'=>$blockVariants, 'type'=>'catalog'));
          $blockVariants = $html;
        }
      }
      Storage::save('getBlocksVariantsToCatalog-'.md5(json_encode($array).$productCount.LANG), $results);
      return $results;
    } else {
      return $cash;
    }
  }

  /**
   * Формирует добавочную строку к названию характеристики,
   * в зависимости от наличия наценки и стоимости.
   * <code>
   * $model = new Models_Product;
   * $result = $model->addMarginToProp(250);
   * echo $result;
   * </code>
   * @param float $margin наценка
   * @param float $rate множитель цены
   * @param string $currency валюта
   * @param string $productCurr валюта товара
   * @return string
   */
  public function addMarginToProp($margin, $rate = 1, $currency = false, $productCurr = null) {
    $originalMargin = $margin;
    $currency = $currency ? $currency : MG::getSetting('currencyShopIso');
    $isPercent = false;
    $percentPath = '';
    $symbol = '+';
    if (!empty($margin)) {
      if ($margin < 0) {
        $symbol = '-';
        $margin = $margin * -1;
      }
      if(stripos($originalMargin, '%')){
        $isPercent = true;
        $percentPath = '%';
      }
      if ($productCurr) {
        $margin = MG::convertCustomPrice($margin, $productCurr, 'set');
      }
    }

    $numberPath = ' '.$symbol.MG::numberFormat(floatval($margin) * $rate);
    if($isPercent){
      $result = $numberPath.$percentPath;
    }
    else{
      $result = $numberPath .' '.MG::getSetting('currency');
    }
    return (!empty($margin) || $margin === 0) ? $result  : '';
  }

  /**
   * Отделяет название характеристики от цены название_пункта#стоимость#.
   * Пример входящей строки: "Красный#300#"
   * <code>
   * $model = new Models_Product;
   * $result = $model->parseMarginToProp('Красный#300#');
   * echo $result;
   * </code>
   * @param string $value строка, которую надо распарсить
   * @return array $array массив с разделенными данными, название пункта и стоимость.
   */
  public function parseMarginToProp($value) {
    $array = array();
    $pattern = "/^(.*)#([\d\.\,-]*%?)#$/";
    preg_match($pattern, $value, $matches);
    if (isset($matches[1]) && isset($matches[2])) {
      $array = array('name' => $matches[1], 'margin' => $matches[2]);
    }
    return $array;
  }

  /**
   * Обновление состояния корзины.
   * Используеться для пересчета корзины и обновления цены в карточке товара ajax'ом
   * <code>
   *   $model = new Models_Product;
   *   $model->calcPrice();
   * </code>
   */
  public function calcPrice() {
    $product = $this->getProduct($_POST['inCartProductId']);
    $currencyRate = MG::getSetting('currencyRate');
    $currencyShopIso = MG::getSetting('currencyShopIso');
    $variantId = 0;
    if (isset($_POST['variant'])) {
      $variants = $this->getVariants($_POST['inCartProductId']);

      $variant = $variants[$_POST['variant']];
      $variantId = $_POST['variant'];
      
      if (!empty($variant['image'])) {
        $product['image'] = $variant['image'];
      } else {
        $product['image'] = $product['image_url'];
      }
      $product['id'] = $variant['product_id'];

      $product['price'] = $variant['price'];
      $product['code'] = $variant['code'];
      $product['count'] = $variant['count'];
      $product['old_price'] = $variant['old_price'];
      $product['weight'] = $variant['weight'];
      $product['weightCalc'] = $variant['weightCalc'];
      $product['price_course'] = $variant['price_course'];
      $product['variant'] = $variant['id'];
    } else {
      $product['variant'] = null;
    }

    
    $product['image_url_orig'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'orig') : '';
    $product['image_url_30'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'small') : '';
    $product['image_url_30_2x'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'small_2x') : '';
    $product['image_url_70'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'big') : '';
    $product['image_url_70_2x'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'big_2x') : '';

    $cart = new Models_Cart;
    $property = $cart->createProperty($_POST);
    $product['currency_iso'] = $product['currency_iso']?$product['currency_iso']:$currencyShopIso;
    $product['price'] = $product['price_course'];

    // $tmpPrice = $product['price'];


    $product['price'] = SmalCart::plusPropertyMargin($product['price'], $property['propertyReal'], $currencyRate[$product['currency_iso']]);

    $product['real_price'] = $product['price'];

    // $product['old_price'] *= $currencyRate[$product['currency_iso']];
    $product['remInfo'] = !empty($_POST['remInfo']) ? $_POST['remInfo'] : '';



    if (NULL_OLD_PRICE && $product['price'] > $product['old_price']) {
      $product['old_price'] = 0;
    }

    $product['count_hr'] = '';
    $convertCountToHR = MG::getSetting('convertCountToHR');
    if (!empty($convertCountToHR) && !MG::isAdmin()) {
      $product['count_hr'] = MG::convertCountToHR($product['count']);
    }

    if (!defined('TEMPLATE_INHERIT_FROM')) {
      $count_layout = MG::layoutManager('layout_count_product', $product);
    } else {
      ob_start();
      component('product/count', $product);
      $count_layout = ob_get_clean();
    }

    $buttonMessage = lang('countMsg1') . ' "' . str_replace("'", "&quot;", $product['title']) . '" ' . lang('countMsg2') . ' "' . $product['code'] . '"' . lang('countMsg3');
    $buttonMessage = urlencode($buttonMessage);
    $buttonMessage = SITE . "/feedback?message=" . str_replace(' ', '&#32;', $buttonMessage)."&code=".$product['code'];
    $response = array(
      'status' => 'success',
      'data' => array(
        'title' => $product['title'],
        'price' => MG::numberFormat($product['price']).' <span class="currency">'.MG::getSetting('currency').'</span>',
        'old_price' => MG::numberFormat($product['old_price']).' '.MG::getSetting('currency'),
        'code' => $product['code'],
        'count' => $product['count'],
        'count_hr' => $product['count_hr'],
        'price_wc' => $product['price'],
        'old_price_wc' => $product['old_price'],
        'real_price' => $product['real_price'],
        'weight' => $product['weight'],
        'weightCalc' => $product['weightCalc'],
        'image_orig' => $product['image_url_orig'],
        'image_thumbs' => array(
          '30' => $product['image_url_30'],
          '2x30' => $product['image_url_30_2x'],
          '70' => $product['image_url_70'],
          '2x70' => $product['image_url_70_2x'],
        ),
        'count_layout' => $count_layout,
        'actionInCatalog' => MG::getSetting('actionInCatalog'),
        'buttonMessage' => $buttonMessage,
      )
    );
    MG::ajaxResponse($response);
  }

  /**
   * Возвращает оригинал и все варианты миниатюр изображений варианта
   * Используется Ajax'ом при изменении варианта в карточке и миникарточке товара
   */
  public function getVariantImages() {
    $product = $this->getProduct($_POST['productId']);

    if (isset($_POST['variant'])) {
      $variants = $this->getVariants($_POST['productId']);
      $variant = $variants[$_POST['variant']];

      $product['image'] = $variant['image'];
      $product['id'] = $variant['product_id'];
    } else {
      $product['variant'] = null;
    }

    $product['image_url_orig'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'orig') : '';
    $product['image_url_30'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'small') : '';
    $product['image_url_30_2x'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'small_2x') : '';
    $product['image_url_70'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'big') : '';
    $product['image_url_70_2x'] = !empty($product['image']) ? mgImageProductPath($product['image'], $product['id'], 'big_2x') : '';
    $response = array(
      'status' => 'success',
      'data' => array(
        'image_orig' => $product['image_url_orig'],
        'image_thumbs' => array(
          '30' => $product['image_url_30'],
          '2x30' => $product['image_url_30_2x'],
          '70' => $product['image_url_70'],
          '2x70' => $product['image_url_70_2x'],
        )
      )
    );
    echo json_encode($response);
    exit;
  }

  /**
   * Возвращает набор вариантов товара.
   * <code>
   * $productId = 25;
   * $model = new Models_Product;
   * $variants = $model->getVariants($productId);
   * viewData($variants);
   * </code>
   * @param int $id id продукта для поиска его вариантов
   * @param string|bool $title_variants название варианта продукта для поиска его вариантов
   * @param bool $sort использовать ли сортировку результатов (из настройки 'filterSortVariant')
   * @return array $array массив с параметрами варианта.
   */
  public function getVariants($id, $title_variants = false, $sort = false, $forceNullVariants = false) {
    $results = array();

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $results, $args);
  }

  /**
   * Возвращает массив id характеристик товара, которые не нужно выводить в карточке.
   * <code>
   * $result = Models_Product::noPrintProperty($productId);
   * viewData($result);
   * </code>
   * @return array $array - массив с id.
   */
  public function noPrintProperty() {
    $results = array();

    $res = DB::query('
      SELECT  `id`
      FROM `'.PREFIX.'property`     
      WHERE `activity` = 0');

    while ($row = DB::fetchAssoc($res)) {
      $results[] = $row['id'];
    }

    return $results;
  }

  /**
   * Возвращает HTML блок связанных товаров.
   * <code>
   * $args = array(
   *  'product' => 'CN182,В-500-1', // артикулы связанных товаров
   *  'category' => '2,4' // ID связанных категорий
   * );
   * $model = new Models_Product;
   * $result = $model->createRelatedForm($args);
   * echo $result;
   * </code>
   * @param array $args массив с данными о товарах
   * @param string $title заголовок блока
   * @param string $layout используемый лэйаут
   * @return string
   */
  public function createRelatedForm($args,$title='С этим товаром покупают', $layout = 'layout_related') {
    $result = '';
    if($args && (!empty($args['product']) || !empty($args['category']))) {
      $data['title'] = $title;

      $catalogModel = new Models_Catalog;

      // выводить ли товар если его нет в наличии
      if(MG::getSetting('printProdNullRem') == "true") {
        $forSameProdFilter = ' and (p.count <> 0 or pv.count <> 0)';
      } else {
        $forSameProdFilter = '';
      }

      // получение товаров по кодам товаров
      $catalogItemsByCodes = [];
      $stringRelated = ' null';
      $sortRelated = [];
      if (!empty($args['product'])) {
        foreach (explode(',',$args['product']) as $item) {
          $stringRelated .= ','.DB::quote($item);
          $sortRelated[$item] = $item;
        }
        $stringRelated = substr($stringRelated, 1);
        $catalogDataByCodes = $catalogModel->getListByUserFilter(100, ' p.code IN ('.$stringRelated.') and p.activity = 1'.$forSameProdFilter);
        $catalogItemsByCodes = $catalogDataByCodes['catalogItems'];
      }

      // получение товаров по категориям
      $catalogItemsByCats = [];
      if (!empty($args['category'])) {
        $stringRelatedCat = ' null';
        foreach (explode(',',$args['category']) as $item) {
          $stringRelatedCat .= ','.DB::quote($item);
        }
        $stringRelatedCat = substr($stringRelatedCat, 1);
        $catalogDataByCats = $catalogModel->getListByUserFilter(100, ' p.`cat_id` IN ('.$stringRelatedCat.') and p.activity = 1'.$forSameProdFilter);
        $catalogItemsByCats = $catalogDataByCats['catalogItems'];
        shuffle($catalogItemsByCats);
      }

      $catalogItems = array_merge($catalogItemsByCodes, $catalogItemsByCats);
      $catalogItemsIds = [];
      if (empty($catalogItems)) {
        $catalogItems = [];
        $sortRelated = [];
      } else {
        foreach ($catalogItems as $catalogItem) {
          if ($catalogItem['id']) {
            $catalogItemsIds[] = $catalogItem['id'];
          }
        }
      }


      if (!empty($catalogItemsIds)) {
        $blocksVariants = $this->getBlocksVariantsToCatalog($catalogItemsIds, defined('TEMPLATE_INHERIT_FROM'));
      } else {
        $blocksVariants = null;
      }

      $blockedProp = $this->noPrintProperty();
      if (empty($blockedProp)) {
        $blockedProp = [];
      }

      foreach ($catalogItems as $k => $catalogItem) {
        if (!empty($catalogItem['variants'])) {
          for($i = 0; $i < count($catalogItem['variants']); $i++) {
            if($catalogItem['variants'][$i]['count'] == 0) {
              $catalogItem['variants'][] = $catalogItem['variants'][$i];
              unset($catalogItem['variants'][$i]);
            }
          }
          $catalogItems[$k]['variants'] = array_values($catalogItem['variants']);
        } else {
          $catalogItems[$k]['variants'] = array();
        }
        
        $imagesUrl = explode("|", $catalogItem['image_url']);
        $catalogItems[$k]["image_url"] = "";

        if (!empty($imagesUrl[0])) {
          $catalogItems[$k]["image_url"] = $imagesUrl[0];
        }

        $catalogItems[$k]['title'] = MG::modalEditor('catalog', $catalogItem['title'], 'edit', $catalogItem["id"]);

        if (
          (
            $catalogItems[$k]['count'] == 0 &&
            empty($catalogItems[$k]['variants'])
          ) ||
          (
            !empty($catalogItems[$k]['variants']) &&
            $catalogItems[$k]['variants'][0]['count'] == 0
          ) ||
          MG::getSetting('actionInCatalog')=='false'
        ) {
          if (!defined('TEMPLATE_INHERIT_FROM')) {
            $buyButton = MG::layoutManager('layout_btn_more', $catalogItems[$k]);
          } else {
            $buyButton = 'more';
          }
        } else {
          if (!defined('TEMPLATE_INHERIT_FROM')) {
            $buyButton = MG::layoutManager('layout_btn_buy', $catalogItems[$k]);
          } else {
            $buyButton = 'buy';
          }
        }
        
        if(MG::getSetting('showMainImgVar') == 'true') {
          if(isset($catalogItem['variants']) && $catalogItem['variants'][0]['image'] != '') {
            $img = explode('/', $catalogItems[$k]['images_product'][0]);
            $img = end($img);
            $catalogItems[$k]["image_url"] = $catalogItems[$k]['images_product'][0] = str_replace($img, $catalogItem['variants'][0]['image'], $catalogItems[$k]['images_product'][0]);
          }
        }

        // Легкая форма без характеристик.
        $liteFormData = $this->createPropertyForm($param = array(
          'id' => $catalogItem['id'],
          'maxCount' => $catalogItem['count'],
          'productUserFields' => null,
          'action' => "/catalog",
          'method' => "POST",
          'ajax' => true,
          'blockedProp' => $blockedProp,
          'noneAmount' => true,
          'titleBtn' => "В корзину",
          'blockVariants' => isset($blocksVariants[$catalogItem['id']])?$blocksVariants[$catalogItem['id']]:'',
          'buyButton' => $buyButton
        ), 'nope', defined('TEMPLATE_INHERIT_FROM'));

        if (!defined('TEMPLATE_INHERIT_FROM')) {
          $catalogItems[$k]['liteFormData'] = $liteFormData['html'];
          $buyButton = $catalogItems[$k]['liteFormData'];
          $catalogItems[$k]['buyButton'] = $buyButton;
        } else {
          $catalogItems[$k]['liteFormData'] = $liteFormData['propertyData'];
          $catalogItems[$k]['buyButton'] = $buyButton;
        }
      }

      foreach ($catalogItems as $key => $catalogItem) {
        $catalogItemCode = $catalogItem['code'];
        if (!empty($catalogItem['variants'])) {
          $catalogItem["price"] = MG::numberFormat($catalogItem['variants'][0]["price_course"]);
          $catalogItem["old_price"] = $catalogItem['variants'][0]["old_price"];
          $catalogItem["count"] = $catalogItem['variants'][0]["count"];
          $catalogItem["code"] = $catalogItem['variants'][0]["code"];
          $catalogItem["weight"] = $catalogItem['variants'][0]["weight"];
          $catalogItem["price_course"] = $catalogItem['variants'][0]["price_course"];
          $catalogItem["variant_exist"] = $catalogItem['variants'][0]["id"];
        }
        if (NULL_OLD_PRICE && (MG::numberDeFormat($catalogItem["price"]) > MG::numberDeFormat($catalogItem["old_price"]))) {
          $catalogItem["old_price"] = 0;
        }
        $sortRelated[$catalogItemCode] = $catalogItem;
      }

      foreach ($sortRelated as $srProductIndex => $srProduct) {
        if (!is_array($srProduct)) {
          unset($sortRelated[$srProductIndex]);
        }
      }

      $data['products'] = $sortRelated;
      $data['currency'] = MG::getSetting('currency');


      $result = '';

    };


    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Конвертирование стоимости товаров по заданному курсу.
   * <code>
   * $model = new Models_Product;
   * $model->convertToIso('USD', array(2, 3, 4));
   * </code>
   * @param string $iso валюта в которую будет производиться конвертация.
   * @param array $productsId массив с id продуктов.
   */
  public function convertToIso($iso,$productsId=array()) {

    //$productsId = implode(',', $productsId);
    //if(empty($productsId)) {$productsId = 0;};

    // вычислим соотношение валют имеющихся в базе товаров к выбранной для замены
    // вычисление производится на основе имеющихся данных по отношению в  валюте магазина
    $currencyShort = MG::getSetting('currencyShort');
    $currencyRate = MG::getSetting('currencyRate');
    $currencyShopIso = MG::getSetting('currencyShopIso');

    // если есть непривязанные к валютам товары, то  назначаем им текущую валюту магазина
    DB::query('
      UPDATE `'.PREFIX.'product` SET 
            `currency_iso` = '.DB::quote($currencyShopIso).'
      WHERE `currency_iso` =  "" AND `id` IN ('.DB::quoteIN($productsId).')');
    DB::query('
      UPDATE `'.PREFIX.'product_variant` SET 
            `currency_iso` = '.DB::quote($currencyShopIso).'
      WHERE `currency_iso` =  "" AND `id` IN ('.DB::quoteIN($productsId).')');

    // запоминаем базовое соотношение курсов к валюте магазина
    $rateBaseArray = $currencyRate;
    $rateBase = $currencyRate[$iso];
    // создаем новое соотношение валют по отношению в выбранной для конвертации
    foreach ($currencyRate as $key => $value) {
        if(!empty($rateBase)) {
          $currencyRate[$key] = $value / $rateBase;
        }
    }
    $currencyRate[$iso] = 1;

    // пересчитываем цену, старую цену и цену по курсу для выбранных товаров
    foreach ($currencyRate as $key => $rate) {
      DB::query('
      UPDATE `'.PREFIX.'product`
      SET `price`= ROUND(`price`*'.DB::quoteFloat($rate,TRUE).',2),
          `price_course`= ROUND(`price`*'.DB::quoteFloat(($rateBaseArray[$iso]?$rateBaseArray[$iso]:1),TRUE).',2)
      WHERE currency_iso = '.DB::quote($key).' AND `id` IN ('.DB::quoteIN($productsId).')');

      // также и в вариантах
      DB::query('
      UPDATE `'.PREFIX.'product_variant`
       SET `price`= ROUND(`price`*'.DB::quoteFloat($rate,TRUE).',2),
          `price_course`= ROUND(`price`*'.DB::quoteFloat(($rateBaseArray[$iso]?$rateBaseArray[$iso]:1),TRUE).',2)
      WHERE currency_iso = '.DB::quote($key).' AND `product_id` IN ('.DB::quoteIN($productsId).')');

      // пересчитываем оптовые цены товару
      DB::query('UPDATE '.PREFIX.'wholesales_sys AS h
        LEFT JOIN '.PREFIX.'product AS p ON p.id = h.product_id
        SET h.price = ROUND(h.price * '.DB::quoteFloat($rate,TRUE).',2) 
        WHERE p.id IN ('.DB::quoteIN($productsId).') AND p.currency_iso = '.DB::quote($key));

    }

    // всем выбранным продуктам изменяем ISO
     DB::query('
      UPDATE `'.PREFIX.'product`
      SET `currency_iso` = '.DB::quote($iso).'
      WHERE `id` IN ('.DB::quoteIN($productsId).')');

     DB::query('
      UPDATE `'.PREFIX.'product_variant`
      SET `currency_iso` = '.DB::quote($iso).'
      WHERE `product_id` IN ('.DB::quoteIN($productsId).')');
  }

   /**
   * Обновления цены товаров в соответствии с курсом валюты.
   * <code>
   * $model = new Models_Product;
   * $model->updatePriceCourse('USD', array(2, 3, 4));
   * </code>
   * @param string $iso валюта в которую будет производиться конвертация.
   * @param array $listId массив с id продуктов.
   */
  public function updatePriceCourse($iso,$listId = array()) {

     if(empty($listId)) {$listId = 0;}
     else{
      if (is_array($listId) || is_object($listId)){
        foreach ($listId as $key => $value) {
          $listId[$key] = intval($value);
        }
      }
       //$listId = implode(',', $listId);
     }

    // вычислим соотношение валют имеющихся в базе товаров к выбранной для замены
    // вычисление производится на основе имеющихся данных по отношению в  валюте магазина
    $currencyShort = MG::getSetting('currencyShort');
    $currencyRate = unserialize(stripcslashes(MG::getOption('currencyRate')));
    $currencyShopIso = MG::getOption('currencyShopIso');
    $recalcForeignCurrencyOldPrice = MG::getSetting('recalcForeignCurrencyOldPrice');


    $rate = $currencyRate[$iso];
    

    $where = '';
    if(!empty($listId)) {
      $where =' AND `id` IN ('.DB::quoteIN($listId).')';
    }

    $whereVariant = '';
    if(!empty($listId)) {
      $whereVariant =' AND `product_id` IN ('.DB::quoteIN($listId).')';
    }

    DB::query('
     UPDATE `'.PREFIX.'product` SET 
           `currency_iso` = '.DB::quote($currencyShopIso).'
     WHERE `currency_iso` = "" '.$where);

    foreach ($currencyRate as $key => $value) {
        if(!empty($rate)) {
          $currencyRate[$key] = $value / $rate;
        }
    }
    $currencyRate[$iso] = 1;
    //Обновление старой цены при изменении курса валют
    foreach ($currencyRate as $key => $rate) {
      $sql = 'SELECT `id`, `price`, `old_price`, `price_course`, `currency_iso` FROM `'.PREFIX.'product` WHERE currency_iso = '.DB::quote($key).' AND `old_price` > 0 '.$where;
      $res = DB::query($sql);
      if ($recalcForeignCurrencyOldPrice === 'true') {
        while($row = DB::fetchAssoc($res)) {
          if($row['currency_iso'] == $currencyShopIso) continue;
          $priceRateCoursOld = $row['price_course']/$row['price'];
          if($priceRateCoursOld != 0){
            $oldPriceInCurrens = round($row['old_price']/$priceRateCoursOld, 2);
            $newOldPrice = round($oldPriceInCurrens*$rate,2);
            DB::query('UPDATE `'.PREFIX.'product` SET `old_price` = '.DB::quote($newOldPrice).' WHERE `id` = '.DB::quote($row['id']));
          }
        }
      }

      DB::query('
      UPDATE `'.PREFIX.'product` 
        SET `price_course`= ROUND(`price`*'.DB::quote((float)$rate,TRUE).',2)          
      WHERE currency_iso = '.DB::quote($key).' '.$where);

      DB::query('
      UPDATE `'.PREFIX.'product_variant` 
        SET `price_course`= ROUND(`price`*'.DB::quote((float)$rate,TRUE).',2)         
      WHERE currency_iso = '.DB::quote($key).' '.$whereVariant);
    }
  }

   /**
   * Удаляет картинки вариантов товара.
   * <code>
   * $model = new Models_Product;
   * $model->deleteImagesVariant(4);
   * </code>
   * @param int $productId ID товара
   * @return bool
   */
  public function deleteImagesVariant($productId) {
    $imagesArray = array();
    // Удаляем картинки продукта из базы.
    $res = DB::query('
      SELECT image
      FROM `'.PREFIX.'product_variant` 
      WHERE product_id = '.DB::quote($productId) );
    while($row = DB::fetchAssoc($res)) {
      $imagesArray[] = $row['image'];
    }
    $this->deleteImagesProduct($imagesArray, $productId);
    return true;
  }

  /**
   * Подготавливает названия изображений товара.
   * <code>
   *   $model = new Models_Product;
   *   $res = $model->prepareImageName($product);
   *   viewData($res);
   * </code>
   * @param array $product массив с товаром
   * @return array
   */
  public function prepareImageName($product) {
    $result = $product;

    $images = explode("|", $result['image_url']);
    foreach($images as $cell=>$image) {
      $pos = strpos($image, 'no-img');
      if($pos || $pos === 0) {
        unset($images[$cell]);
      } else {
        $images[$cell] = basename($image);
      }
    }
    $result['image_url'] = implode('|', $images);

    if (isset($result['variants']) && is_array($result['variants'])) {
      foreach($result['variants'] as $cell=>$variant) {
        $images = array();
        if(empty($variant['image'])) {
          continue;
        }

        $pos = strpos($variant['image'], 'no-img');
        if($pos || $pos === 0) {
          unset($result['variants'][$cell]['image']);
        } else {
          if (strpos($variant['image'], DS.'thumbs'.DS)) {
            $variant['image'] = str_replace(array('thumbs'.DS.'30_', 'thumbs'.DS.'70_'), '', $variant['image']);
          }

          $images[] = basename($variant['image']);
        }
        $result['variants'][$cell]['image'] = implode('|', $images);
      }
    }

    return $result;
  }

  public function setZeroStock($productId) {
    if (MG::enabledStorage()) {
      $setZeroStockSql = 'UPDATE `'.PREFIX.'product_on_storage` '.
        'SET `count` = 0 '.
        'WHERE `product_id` = '.DB::quoteInt($productId);
      DB::query($setZeroStockSql);
    }
    $setProductZeroStockSql = 'UPDATE `'.PREFIX.'product` '.
      'SET `count` = 0, storage_count = 0 '.
      'WHERE `id` = '.DB::quoteInt($productId);
    $setVariantsZeroStockSql = 'UPDATE `'.PREFIX.'product_variant` '.
      'SET `count` = 0 '.
      'WHERE `product_id` = '.DB::quoteInt($productId);
    DB::query($setProductZeroStockSql);
    DB::query($setVariantsZeroStockSql);
  }

  /**
   * Копирует изображения товара в новую структуру хранения.
   *
   * @param array $images - массив изображений
   * @param int $productId - id товара
   * @param string $path - папка в которой лежат исходные изображения
   * @param bool $removeOld - флаг удаления изображений из папки $path после копирования в новое место
   * @return void
   */
  public function movingProductImage($images, $productId, $path='uploads', $removeOld = true) {
    if(empty($images)) {
      return false;
    }
    $ds = DS;
    $dir = floor($productId/100).'00';
    @mkdir(SITE_DIR.'uploads'.$ds.'product', 0755);
    @mkdir(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir, 0755);
    @mkdir(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId, 0755);
    @mkdir(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs', 0755);
    
    // получаем актуальные изображения для товара и его вариантов
    $variantsImgs = array();
    $sqlFindVariantsImgs = 'SELECT `image` FROM `'.PREFIX.'product_variant` WHERE `product_id` ='.DB::quoteInt($productId);
    $res = DB::query($sqlFindVariantsImgs);
    while($row = DB::fetchAssoc($res)) {
      $variantsImgs[] = $row['image'];
    }
    
    $productImages = array();
    $sqlProductImages = 'SELECT `image_url` FROM `'.PREFIX.'product` WHERE `id` ='.DB::quoteInt($productId);
    $res = DB::query($sqlProductImages);

    if($row = DB::fetchAssoc($res)) {
      $productImages =  explode('|', $row['image_url']);
    }

    foreach($images as $cell=>$image) {
      $pos = strpos($image, '_-_time_-_');

      if ($pos) {
        if (MG::getSetting('addDateToImg') == 'true') {
          $tmp1 = explode('_-_time_-_', $image);
          $tmp2 = strrpos($tmp1[1], '.');
          $tmp1[0] = date("_Y-m-d_H-i-s", substr_replace($tmp1[0], '.', 10, 0));
          $imageClear = substr($tmp1[1], 0, $tmp2).$tmp1[0].substr($tmp1[1], $tmp2);
        }
        else{
          $imageClear = substr($image, ($pos+10));
        }
      }
      else{
        $imageClear = $image;
      }

      // удаляем лишние файлы в папке которые уже не относятся к товару или вариантам
       if (file_exists(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds)) {
        foreach (glob(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'*') as $productFile) {
          $fileName = basename($productFile);
          if(!in_array($fileName, $variantsImgs) && !in_array($fileName, $productImages) && $fileName != 'thumbs'){
              unlink($productFile);
          }
          elseif($fileName === 'thumbs'){
            foreach (glob(SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$fileName.$ds.'*') as $productFileThumbs) {
              $fileName = preg_replace(['/^2x_30_/', '/^2x_70_/', '/^30_/', '/^70_/'],'',basename($productFileThumbs));          
              if(!in_array($fileName, $variantsImgs) && !in_array($fileName, $productImages)){
                unlink($productFileThumbs);
              }
            }
          }
        }
      }

      if(
        is_file($path.$ds.$image) &&
        copy($path.$ds.$image, SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$imageClear)
      ) {
        // Поворачиваем скопированное изображение в соответствии с meta-информацией (exif)
        $fullName = explode('.', $imageClear);
        $ext = array_pop($fullName);
        $exifRotateImagesExts = [
          'jpeg',
          'jpg',
          'png'
        ];
        if (
          MG::getSetting('exifRotate') === 'true' &&
          in_array(strtolower($ext), $exifRotateImagesExts)
        ) {
          $imagePath = SITE_DIR.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$imageClear;
          if ($ext === 'png') {
            $imageForRotation = imagecreatefrompng($imagePath);
            imageAlphaBlending($imageForRotation, true);
            $imageForRotation = Upload::rotateImageByExif($imageForRotation, $imagePath);
            imageSaveAlpha($imageForRotation, true);
            imagepng($imageForRotation, $imagePath);
          } else {
            $imageForRotation = imagecreatefromjpeg($imagePath);
            $imageForRotation = Upload::rotateImageByExif($imageForRotation, $imagePath);
            imagejpeg($imageForRotation, $imagePath);
          }
        }

        $productImages[] = $imageClear;
        if(
          is_file($path.$ds.'thumbs'.$ds.'30_'.$image) &&
          copy($path.$ds.'thumbs'.$ds.'30_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'30_'.$imageClear) && $removeOld
        ) {
          unlink($path.$ds.'thumbs'.$ds.'30_'.$image);
        }
        
        if(
          is_file($path.$ds.'thumbs'.$ds.'2x_30_'.$image) &&
          copy($path.$ds.'thumbs'.$ds.'2x_30_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'2x_30_'.$imageClear) && $removeOld
        ) {
          unlink($path.$ds.'thumbs'.$ds.'2x_30_'.$image);
        }

        if(
          is_file($path.$ds.'thumbs'.$ds.'70_'.$image) &&
          copy($path.$ds.'thumbs'.$ds.'70_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'70_'.$imageClear) && $removeOld
        ) {
          unlink($path.$ds.'thumbs'.$ds.'70_'.$image);
        }

        if(
          is_file($path.$ds.'thumbs'.$ds.'2x_70_'.$image) &&
          copy($path.$ds.'thumbs'.$ds.'2x_70_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'2x_70_'.$imageClear) && $removeOld
        ) {
          unlink($path.$ds.'thumbs'.$ds.'2x_70_'.$image);
        }

        if($removeOld) {
          unlink($path.$ds.$image);
        }
      }elseif(
        is_file('uploads'.$ds.$image) &&
        copy('uploads'.$ds.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$imageClear)
      ) {
        if(
          is_file('uploads'.$ds.'thumbs'.$ds.'30_'.$image) &&
          copy('uploads'.$ds.'thumbs'.$ds.'30_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'30_'.$imageClear) && $removeOld
        ) {
          unlink('uploads'.$ds.'thumbs'.$ds.'30_'.$image);
        }

        if(
          is_file('uploads'.$ds.'thumbs'.$ds.'70_'.$image) &&
          copy('uploads'.$ds.'thumbs'.$ds.'70_'.$image, 'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.'70_'.$imageClear) && $removeOld
        ) {
          unlink('uploads'.$ds.'thumbs'.$ds.'70_'.$image);
        }

        if($removeOld) {
          unlink('uploads'.$ds.$image);
        }
      }
    }
  }


  public function getProductsTotalCount() {
    $totalCount = 0;
    $totalCountSql = 'SELECT COUNT(`id`) as total_count '.
      'FROM `'.PREFIX.'product`';
    $totalCountResult = DB::query($totalCountSql);
    if ($totalCountRow = DB::fetchAssoc($totalCountResult)) {
      $totalCount = intval($totalCountRow['total_count']);
    }
    return $totalCount;
  }

  public function getProductIdByExternalId($externalId) {
    $productId = null;
    $productIdSql = 'SELECT `id` '.
      'FROM `'.PREFIX.'product` '.
      'WHERE `1c_id` = '.DB::quote($externalId);
    $productIdResult = DB::query($productIdSql);
    if ($productIdRow = DB::fetchAssoc($productIdResult)) {
      $productId = intval($productIdRow['id']);
    }
    return $productId;
  }

  public function updateStorageCount($productId, $storageId, $count, $variantId = null) {
    $result = false;
    $storageCountIdSql = 'SELECT `id` '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `product_id` = '.DB::quoteInt($productId).' '.
        'AND `storage` = '.DB::quote($storageId);

    $variantWhereClause = 'AND `variant_id` = '.DB::quoteInt($variantId);
    if (!$variantId) {
      $variantWhereClause = 'AND (`variant_id` = 0 OR ISNULL(`variant_id`))';
    }

    $storageCountIdSql .= ' '.$variantWhereClause;
    
    $storageCountIdResult = DB::query($storageCountIdSql);
    if ($storageCountIdRow = DB::fetchAssoc($storageCountIdResult)) {
      $storageCountId = intval($storageCountIdRow['id']);
      $updateCountSql = 'UPDATE `'.PREFIX.'product_on_storage` '.
        'SET `count` = '.DB::quoteFloat($count).' '.
        'WHERE `id` = '.DB::quoteInt($storageCountId);
      $result = DB::query($updateCountSql);
    } else {
      $countData = [
        'NULL',
        DB::quote($storageId),
        DB::quoteInt($productId),
        DB::quoteInt($variantId),
        DB::quoteFloat($count),
      ];
      $createCountSql = 'INSERT INTO `'.PREFIX.'product_on_storage` '.
        'VALUES ('.implode(', ', $countData).')';
      $result = DB::query($createCountSql);
    }
    return $result;
  }

  public function setNewStorageCount($productId, $storageId, $count, $variantId = null) {
    if ($variantId) {
      if (!$this->deleteStorageRecordsVariantOnly($productId, $variantId, $storageId)) {
        return false;
      }
    } else {
      if (!$this->deleteStorageRecordsProductOnly($productId, $storageId)) {
        return false;
      }
    }
    if (!$this->addStorageRecord($productId, $storageId, $count, $variantId)) {
      return false;
    }
    return true;
  }

  public function deleteStorageRecordsAll($productId, $storageId) {
    $whereParts = [
      '`product_id` = '.DB::quoteInt($productId),
    ];
    if ($storageId !== 'all') {
      $whereParts[] = '`storage` = '.DB::quote($storageId);
    }
    $whereClause = implode(' AND ', $whereParts);
    $result = $this->deleteStorageRecords($whereClause);
    return $result;
  }

  public function deleteStorageRecordsProductOnly($productId, $storageId) {
    $whereParts = [
      '`product_id` = '.DB::quoteInt($productId),
      '(`variant_id` = 0 OR `variant_id` IS NULL)'
    ];
    if ($storageId !== 'all') {
      $whereParts[] = '`storage` = '.DB::quote($storageId);
    }
    $whereClause = implode(' AND ', $whereParts);
    $result = $this->deleteStorageRecords($whereClause);
    return $result;
  }

  public function deleteStorageRecordsVariantOnly($productId, $variantId, $storageId) {
    $whereParts = [
      '`product_id` = '.DB::quoteInt($productId),
      '`variant_id` = '.DB::quoteInt($variantId),
    ];
    if ($storageId !== 'all') {
      $whereParts[] = '`storage` = '.DB::quote($storageId);
    }
    $whereClause = implode(' AND ', $whereParts);
    $result = $this->deleteStorageRecords($whereClause);
    return $result;
  }

  public function deleteStorageRecordsAllVariants($productId, $storageId) {
    $whereParts = [
      '`product_id` = '.DB::quoteInt($productId),
      '`variant_id` != 0',
      '`variant_id` IS NOT NULL',
    ];
    if ($storageId !== 'all') {
      $whereParts[] = '`storage` = '.DB::quote($storageId);
    }
    $whereClause = implode(' AND ', $whereParts);
    $result = $this->deleteStorageRecords($whereClause);
    return $result;
  }

  private function deleteStorageRecords($whereClause) {
    $deleteStorageRecordSql = 'DELETE '.
      'FROM `'.PREFIX.'product_on_storage`';
    if ($whereClause) {
      $deleteStorageRecordSql .= ' WHERE '.$whereClause;
    }
    $result = DB::query($deleteStorageRecordSql);
    return $result;
  }

  public function addStorageRecord($productId, $storageId, $count, $variantId = null) {
    $addStorageRecordValues = [
      'NULL',
      DB::quote($storageId),
      DB::quoteInt($productId),
      DB::quoteInt($variantId),
      DB::quoteFloat($count),
    ];
    $addStorageRecordSql = 'INSERT INTO `'.PREFIX.'product_on_storage` '.
      'VALUES ('.implode(', ', $addStorageRecordValues).')';
    $result = DB::query($addStorageRecordSql);
    return $result;
  }

  public function clearStoragesTable() {
    $truncateStoragesTableSql = 'TRUNCATE `'.PREFIX.'product_on_storage`';
    $result = DB::query($truncateStoragesTableSql);
    return $result;
  }

  public function destroyStorageStocks($storage) {
    $destroyStorageStockSql = 'DELETE '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `storage` = '.DB::quote($storage);
    $result = DB::query($destroyStorageStockSql);
    return $result;
  }

  public function getProductStorageCount($storageId, $productId, $variantId = 0) {
    $result = 0;
    $storageCountSql = 'SELECT `count` '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `storage` = '.DB::quote($storageId).' '.
        'AND `product_id` = '.DB::quoteInt($productId).' '.
        'AND `variant_id` = '.DB::quoteInt($variantId);

    $storageCountResult = DB::query($storageCountSql);
    if ($storageCountRow = DB::fetchAssoc($storageCountResult)) {
      $result = floatval($storageCountRow['count']);
    }

    return $result;
  }

  public function getVariantIdByCode($code) {
    $variantId = null;
    $variantIdSql = 'SELECT `id` '.
      'FROM `'.PREFIX.'product_variant` '.
      'WHERE `code` = '.DB::quote($code);
    $variantIdResult = DB::query($variantIdSql);
    if ($variantIdRow = DB::fetchAssoc($variantIdResult)) {
      $variantId = intval($variantIdRow['id']);
    }
    return $variantId;
  }

  public function getProductStorageData($productId, $variantId) {
    $storageArray = [];
    $storagesData = $this->getProductStoragesData($productId, $variantId);
    if (empty($storagesData)) {
      $storagesData = [];
    }
    foreach ($storagesData as $storageId => $storageCount) {
      $storageArray[] = [
        'storage' => $storageId,
        'count' => $storageCount,
      ];
    }
    $storagesSettings = MG::getSetting('storages_settings', true);
    $writeOffProc = intval($storagesSettings['writeOffProc']);
    $storage = strval(array_keys($storagesData)[0]);
    if ($writeOffProc === 2) {
      $storage = $storagesSettings['storagesOrderArray'][0]['storagId'];
    }
    if ($writeOffProc === 3) {
      $storage = $storagesSettings['mainStorage'];
    }
    $result = [
      'storageArray' => $storageArray,
      'storage' => $storage,
    ];
    return $result;
  }

  public function getProductStoragesData($productId, $variantId = 0) {
    $result = [];
    $storagesCountSql = 'SELECT `storage`, `count` '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `product_id` = '.DB::quoteInt($productId);
    
    $variantWhereClause = 'AND `variant_id` = '.DB::quoteInt($variantId);
    if (!$variantId) {
      $variantWhereClause = 'AND (`variant_id` = 0 OR ISNULL(`variant_id`))';
    }

    $storagesCountSql .= ' '.$variantWhereClause;

    $storagesCountResult = DB::query($storagesCountSql);
    while ($storagesCountRow = DB::fetchAssoc($storagesCountResult)) {
      $storageId = strval($storagesCountRow['storage']);
      $count = floatval($storagesCountRow['count']);
      $result[$storageId] = $count;
    }
    return $result;
  }

  public function getProductStorageTotalCount($productId, $variantId = null) {
    $result = null;
    $checkProductInfSql = 'SELECT `id` '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `product_id` = '.DB::quoteInt($productId).' '.
        'AND `variant_id` = '.DB::quoteInt($variantId).' '.
        'AND `count` = -1';
    $checkProductInfResult = DB::query($checkProductInfSql);
    if (DB::fetchAssoc($checkProductInfResult)) {
      $result = -1;
      return $result;
    }

    $totalCountSql = 'SELECT SUM(`count`) as total_count '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `product_id` = '.DB::quoteInt($productId).' '.
        'AND `variant_id` = '.DB::quoteInt($variantId);
    $totalCountResult = DB::query($totalCountSql);
    if ($totalCountRow = DB::fetchAssoc($totalCountResult)) {
      $result = floatval($totalCountRow['total_count']);
    }
    return $result;
  }

  public function decreaseCountProductOnStorage($product, $storage = 'all') {
    $productId = intval($product['id']);
    $variantId = intval($product['variantId']);
    $count = floatval($product['count']);

    // Если передан склад, с которого нужно списать
    if (
      $storage &&
      $storage !== 'all'
    ) {
      // Сначала проверяем сколько товара доступно на этом складе
      $countOnCurrentStorage = $this->getProductStorageCount($storage, $productId, $variantId);
      // Если на переданном складе достаточно товара, то списываем с него
      if ($countOnCurrentStorage >= $count) {
        $this->updateStorageCount($productId, $storage, $countOnCurrentStorage - $count, $variantId);
        $this->recalculateStorages($productId);
        $this->resetLastUpdate($product);
        return true;
      }
    }

    // Если не передан склад, с которого нужно списать, то тут уже ориентируемся на настройку порядка списываения со складов
    $storagesSettings = MG::getSetting('storages_settings', true);
    $writeOffOrder = 1;
    if (!empty($storagesSettings['writeOffProc'])) {
      $writeOffOrder = intval($storagesSettings['writeOffProc']);
    }

    switch ($writeOffOrder) {
      case 1: // С первого, на котором есть все
        break;
      case 2: // В заданном порядке
        break;
      case 3: // С основого
        break;
      default:
        return false;
    }


    return false;
  }

  public function orderDecreaseProductStorageCountOld($storage = 'all', $adminCartItems = null) {
    if (!MG::enabledStorage()) {
      return false;
    }
    $cartItems = $_SESSION['cart'];
    if ($adminCartItems) {
      $cartItems = $adminCartItems;
    }

    foreach ($cartItems as $cartItem) {
      $this->decreaseCountProductOnStorage($cartItem);
    }

    return $cartItems;

    // $cartItems = $_SESSION['cart'];
    // $storagesSettings = MG::getSetting('storages_settings', true);

    // foreach ($cartItems as $cartItem) {
    //   $productId = intval($cartItem['id']);
    //   $variantId = intval($cartItem['variantId']);
    //   $count = floatval($cartItem['count']);

    //   // Если используется конкретный склад
    //   if (
    //     $storage &&
    //     $storage !== 'all'
    //   ) {
    //     // Проверяем сколько товара на этом складе
    //     $currentStorageCount = $this->getProductStorageCount($storage, $productId, $variantId);
    //     // Если товар в полном объёме есть на этом складе
    //     if ($currentStorageCount >= $count) {
    //       // То списываем с него
    //       $this->decreaseCountProductOnStorage($cartItem, $storage);
    //     }
    //   }
    // }
  }

  public function orderDecreaseProductStorageCount($storage = null, $cartItems = null) {
    // Если заказ из админки, то товары передаются в cartItems,
    // если из публички, то они находятся в сессии
    if ($cartItems === null) {
      $cartItems = $_SESSION['cart'];
    }
    if (!$cartItems) {
      return false;
    }

    if ($storage === 'all') {
      $storage = null;
    }

    // Если передан склад, с которого нужно списать
    if ($storage) {
      $fromExactStorage = true;
      // То перебираем товары и проверяем, хватит ли количества на складах, чтобы списать
      foreach ($cartItems as $product) {
        $productId = intval($product['id']);
        $variantId = intval($product['variantId']);
        $count = floatval($product['count']);
        $storageCount = $this->getProductStorageCount($storage, $productId, $variantId);
        if ($storageCount >= 0 && $storageCount < $count) {
          $fromExactStorage = false;
          break;
        }
      }
      // Если количества точно хватает, то списываем эти товары со складов
      // И добавляем каждому товару запись о том, с какого склада сколько его было списано
      if ($fromExactStorage) {
        foreach ($cartItems as &$product) {
          $productId = intval($product['id']);
          $variantId = intval($product['variantId']);
          $count = floatval($product['count']);
          $storageCount = $this->getProductStorageCount($storage, $productId, $variantId);
          $newCount = $storageCount - $count;
          if (floatval($storageCount) === floatval(-1)) {
            $newCount = -1;
          }
          $this->updateStorageCount($productId, $storage, $newCount, $variantId);
          $this->recalculateStoragesById($productId);
          $product['storage_id'] = [
            $storage => $count,
          ];
        }
        return $cartItems;
      }
    }

    // Настройки складов
    $storagesSettings = MG::getSetting('storages_settings', true);

    $writeOffAlg = intval($storagesSettings['writeOffProc']); // В каком порядке списывать со складов
    $writeOffWithoutMainAlg = intval($storagesSettings['storagesAlgorithmWithoutMain']); // В каком порядке списывать со складов, если нет на основном
    $storagesOrder = $storagesSettings['storagesOrderArray']; // Сортировка складов
    $mainStorage = $storagesSettings['mainStorage']; // Основной склад
    $useOneStorage = MG::getSetting('useOneStorage') === 'true'; // Настройка, запрещающая списывать один заказ с разных складов

    if ($storagesOrder) {
      // Дополнительно отсортировываем массив сортировки складов
      usort($storagesOrder, function ($storageDataA, $storageDataB) {
        $sortA = intval($storageDataA['storageNumver']);
        $sortB = intval($storageDataB['storageNumber']);
        if ($sortA === $sortB) {
          return 0;
        }
        if ($sortA < $sortB) {
          return -1;
        }
        return 1;
      });
    }

    // Этот массив хранит информацию о том, какой товар, в каком объёме, с какого склада можно списать
    $writeOffData = [];

    // Список доступных складов
    $availableStorages = [];
    $storagesDatas = unserialize(stripcslashes(MG::getSetting('storages')));
    foreach ($storagesDatas as $storageData) {
      $storageId = $storageData['id'];
      $availableStorages[$storageId] = $storageId;
    }

    // Сортируем список доступных складов в соответсвии с настройками складов
    $sortedStorages = [];
    switch ($writeOffAlg) {
      case 1: // С первого у которого есть все
        $sortedStorages = $availableStorages; // Эта соритровка работает непосредственно в цикле по товарам, поэтому здесь ничего делать не нужно
        break; 
      case 2: // В заданном порядке
        // Тут в качестве сортировки выступает конкретный список складов
        foreach ($storagesOrder as $storageData) {
          $storageId = $storageData['storagId'];
          $sortedStorages[$storageId] = $storageId;
        }
        break;
      case 3: // С основого
        // Если остальные склады упорядочить в заданном порядке
        if ($writeOffWithoutMainAlg === 2) {
          // В список отсортированных складов сначала записываем основной склад
          $sortedStorages[$mainStorage] = $mainStorage;
          // А затем все остальные из заданного порядка
          foreach ($storagesOrder as $storageData) {
            $storageId = $storageData['storagId'];
            // Кроме основного склада
            if ($storageId === $mainStorage) {
              continue;
            }
            $sortedStorages[$storageId] = $storageId;
          }
        // Если остальные склады упорядочить по "Первый на котором есть все"
        } else {
          // То в начала записываем основной склад, а остальные "как есть",
          // они будут отсортированы уже в процессе перебирания товаров
          $sortedStorages[$mainStorage] = $mainStorage;
          foreach ($availableStorages as $storageId) {
            if ($storageId === $mainStorage) {
              continue;
            }
            $sortedStorages[$storageId] = $storageId;
          }
        }
        break;
    }

    // Перебираем все товары и определяем с какого склада сколько товара можно списать
    foreach ($cartItems as $product) {
      $productId = intval($product['id']);
      $variantId = intval($product['variantId']);
      $inOrderCount = floatval($product['count']);

      // В первую очередь проверяем а хватит ли вообще количества товара на складах
      $totalCount = $this->getProductStorageTotalCount($productId, $variantId);
      // Если на всех складах вместе взятых меньше товара, чем нужно
      if ($totalCount >= 0 && $totalCount < $inOrderCount) {
        // То такой заказ оформить невозможно
        return false;
      }

      foreach ($sortedStorages as $storageId) {
        $storageCount = $this->getProductStorageCount($storageId, $productId, $variantId);
        // Если на складе недостаточно товара, то убираем этот склад из доступных
        if (!$storageCount || ($storageCount >= 0 && $storageCount < $inOrderCount)) {
          unset($availableStorages[$storageId]);
        }
        $writeOffData[$productId][$variantId][$storageId] = $storageCount;
      }
    }

    // Тут будет храниться идентификатор склада, с которого можно списать все товары в заказе
    // Выбран он будет из всех складов с которых можно списать все товары заказа
    // по сортировке первого товара
    $selectedAvailableStorage = null;
    foreach ($writeOffData as $writeOffProduct) {
      foreach ($writeOffProduct as $writeOffVariant) {
        foreach ($writeOffVariant as $writeOffStorage => $writeOffCount) {
          if (in_array($writeOffStorage, $availableStorages)) {
            $selectedAvailableStorage = $writeOffStorage;
            break 3;
          }
        }
      }
    }

    // Если включена настройка списывать только с одного склада
    if ($useOneStorage) {
      // Если нет склада, с которого можно списать все товары заказа
      if (!$selectedAvailableStorage) {
        // То такой заказ невозможен
        return false;
      }
      // А если есть, то перебираем все товары и все варинты и списываем с этого склада
      foreach ($cartItems as &$product) {
        $productId = intval($product['id']);
        $variantId = intval($product['variantId']);
        $inOrderCount = floatval($product['count']);
        $storageCount = floatval($writeOffData[$productId][$variantId][$selectedAvailableStorage]);
        $newCount = $storageCount - $inOrderCount;
        if ($storageCount < 0) {
          $newCount = -1;
        }

        $this->updateStorageCount($productId, $selectedAvailableStorage, $newCount, $variantId);
        $this->recalculateStoragesById($productId);
        $product['storage_id'][$selectedAvailableStorage] = $inOrderCount;
      }
      // Списание успешно завершено, возвращаем список товаров
      return $cartItems;
    }

    // Если списывать товары можно с разных складов
    // То снова ориентируемся на настройки приоритетов списания со складов
    switch($writeOffAlg) {
      case 1:  // С первого у которого есть все
        // Если есть склад, с которого можно списать все товары заказа
        if ($selectedAvailableStorage) {
          // То списываем все товары с него
          foreach ($cartItems as &$product) {
            $productId = intval($product['id']);
            $variantId = intval($product['variantId']);
            $inOrderCount = floatval($product['count']);
            $storageCount = floatval($writeOffData[$productId][$variantId][$selectedAvailableStorage]);
            $newCount = $storageCount - $inOrderCount;
            if ($storageCount < 0) {
              $newCount = -1;
            }
    
            $this->updateStorageCount($productId, $selectedAvailableStorage, $newCount, $variantId);
            $this->recalculateStoragesById($productId);
            $product['storage_id'][$selectedAvailableStorage] = $inOrderCount;
          }
          // Списание успешно завершено, возвращаем список товаров
          return $cartItems;
          // Если такого склада нет
        } else {
          // То списываем просто по порядку с какого склада сколько можно
          foreach ($cartItems as &$product) {
            $productId = intval($product['id']);
            $variantId = intval($product['variantId']);
            $inOrderCount = floatval($product['count']);
            $complete = 0;
            foreach ($writeOffData[$productId][$variantId] as $storageId => $storageCount) {
              $toWriteOffCount = min($inOrderCount - $complete, $storageCount);
              $newCount = $storageCount - $toWriteOffCount;
              if ($storageCount < 0) {
                $toWriteOffCount = $inOrderCount;
                $newCount = -1;
              } else {
                $this->updateStorageCount($productId, $storageId, $newCount, $variantId);
                $product['storage_id'][$storageId] = $toWriteOffCount;
              }
              $complete += $toWriteOffCount;
              if (floatval($complete) >= floatval($inOrderCount)) {
                $this->recalculateStoragesById($productId);
                continue 2;
              }
            }
          }
        }
        break;
      case 2: // В заданном порядке
        // Просто перебираем заготовленный массив
        foreach ($cartItems as &$product) {
          $productId = intval($product['id']);
          $variantId = intval($product['variantId']);
          $inOrderCount = floatval($product['count']);
          $complete = 0;
          foreach ($writeOffData[$productId][$variantId] as $storageId => $storageCount) {
            $toWriteOffCount = min($inOrderCount - $complete, $storageCount);
            $newCount = $storageCount - $toWriteOffCount;
            if ($storageCount < 0) {
              $toWriteOffCount = $inOrderCount;
              $newCount = -1;
            } else {
              $this->updateStorageCount($productId, $storageId, $newCount, $variantId);
              $product['storage_id'][$storageId] = $toWriteOffCount;
            }
            $complete += $toWriteOffCount;
            if (floatval($complete) >= floatval($inOrderCount)) {
              $this->recalculateStoragesById($productId);
              continue 2;
            }
          }
        }
        break;
      case 3: // С основого
        // И снова перебираем товары
        foreach ($cartItems as &$product) {
          $productId = intval($product['id']);
          $variantId = intval($product['variantId']);
          $inOrderCount = floatval($product['count']);

          // Выковыриваем основной склад из общего списка
          // И списываем с него сколько можно
          $mainStorageCount = $writeOffData[$productId][$variantId][$mainStorage];
          if ($mainStorageCount < 0) {
            $product['storage_id'][$mainStorage] = $inOrderCount;
            continue;
          }
          $complete = 0;
          $toWriteOffCount = min($inOrderCount - $complete, $mainStorageCount);
          $newCount = $mainStorageCount - $toWriteOffCount;
          if ($newCount !== $mainStorageCount) {
            $this->updateStorageCount($productId, $mainStorage, $newCount, $variantId);
            $product['storage_id'][$mainStorage] = $toWriteOffCount;
          }
          $complete += $toWriteOffCount;

          // Если списания с основного склада не хватило
          if ($complete < $inOrderCount) {
            // Если установлена сортировка списывать с первого где есть все товары
            // И такой склад есть
            if ($writeOffWithoutMainAlg !== 2 && $selectedAvailableStorage) {
              // То списываем с него
              $selectedAvailableStorageCount = $writeOffData[$productId][$variantId][$selectedAvailableStorage];
              if ($selectedAvailableStorageCount >= 0) {
                $toWriteOffCount = min($inOrderCount - $complete, $selectedAvailableStorageCount);
                $newCount = $selectedAvailableStorageCount - $toWriteOffCount;
                if ($newCount !== $selectedAvailableStorageCount) {
                  $this->updateStorageCount($productId, $selectedAvailableStorage, $newCount, $variantId);
                  $product['storage_id'][$selectedAvailableStorage] = $toWriteOffCount;
                }
                $complete += $toWriteOffCount;
              } else {
                $product['storage_id'][$selectedAvailableStorage] = $inOrderCount;
              }
            }
            // Если и после списывания с "общего" склада не хватило
            if ($complete < $inOrderCount) {
              // То списываем с оставшихся складов
              foreach ($writeOffData[$productId][$variantId] as $storageId => $storageCount) {
                // Только основной и выбранный общий склады уже пропускаем
                if (in_array($storageId, [$mainStorage, $selectedAvailableStorage])) {
                  continue;
                }
                $toWriteOffCount = min($inOrderCount - $complete, $storageCount);
                $newCount = $storageCount - $toWriteOffCount;
                if ($storageCount < 0) {
                  $toWriteOffCount = $inOrderCount;
                  $newCount = -1;
                } else {
                  $this->updateStorageCount($productId, $storageId, $newCount, $variantId);
                  $product['storage_id'][$storageId] = $toWriteOffCount;
                }
                $complete += $toWriteOffCount;
                if (floatval($complete) >= floatval($inOrderCount)) {
                  $this->recalculateStoragesById($productId);
                  continue 2;
                }
              }
            }
            $this->recalculateStoragesById($productId);
          }
        }
        break;
    }
    return $cartItems;
  }

  public function resetLastUpdate($product) {
    $productId = intval($product['id']);
    $variantId = intval($product['variantId']);
    
    $currentDate = date('Y-m-d H:i:s');
    $updateLastUpdateSql = 'UPDATE `'.PREFIX.'product` '.
      'SET `last_updated` = '.DB::quote($currentDate).' '.
      'WHERE `id` = '.DB::quoteInt($productId);
    if ($variantId) {
      $updateLastUpdateSql = 'UPDATE `'.PREFIX.'product_variant` '.
        'SET `last_updated` = '.DB::quote($currentDate).' '.
        'WHERE `id` = '.DB::quoteInt($variantId);
    }

    $result = DB::query($updateLastUpdateSql);
    return $result;
  }

  public function cloneStorageData($oldId, $newId, $newVariantId = 0) {
    $result = true;
    $oldStoragesDataWhereClause = '`product_id` = '.DB::quoteInt($oldId);
    if ($newVariantId) {
      $oldStoragesDataWhereClause = '`variant_id` = '.DB::quoteInt($oldId);
    }
    $oldStoragesDataSql = 'SELECT * '.
      'FROM `'.PREFIX.'product_on_storage`'.
      'WHERE '.$oldStoragesDataWhereClause;
    $oldStoragesDataResult = DB::query($oldStoragesDataSql);
    $insertParts = [];
    while ($oldStorageDataRow = DB::fetchAssoc($oldStoragesDataResult)) {
      $insertParts[] = '('.implode(', ', [
        'NULL',
        DB::quote($oldStorageDataRow['storage']),
        DB::quoteInt($newId),
        DB::quoteInt($newVariantId),
        DB::quoteFloat($oldStorageDataRow['count']),
      ]).')';
    }
    if ($insertParts) {
      $insertSql = 'INSERT INTO '.
        '`'.PREFIX.'product_on_storage` '.
        'VALUES '.implode(', ', $insertParts);
      $result = DB::query($insertSql);
    }
    return $result;
  }

  public function getStoragesCountsByVariantsIds($variantsIds = [], $storage = null) {
    if (!$variantsIds) {
      return [];
    }
    $storagesCount = [];
    $storagesCountSql = 'SELECT `storage`, `count`, `variant_id` '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `variant_id` IN ('.DB::quoteIN($variantsIds).')';
    if ($storage) {
      $storagesCountSql .= ' AND `storage` = '.DB::quote($storage);
    }
    $storagesCountResult = DB::query($storagesCountSql);
    while ($storagesCountRow = DB::fetchAssoc($storagesCountResult)) {
      $storage = strval($storagesCountRow['storage']);
      $variantId = intval($storagesCountRow['variant_id']);
      $count = floatval($storagesCountRow['count']);
      $storagesCount[$variantId][$storage] = $count;
    }
    return $storagesCount;
  }

  public function checkStoragesRecalculation() {
    $checkStorageCountSql = 'SELECT `id` '.
      'FROM `'.PREFIX.'product` '.
      'WHERE `storage_count` IS NOT NULL AND'.
        '`storage_count` != 0 '.
      'LIMIT 1';
    $checkStorageCountResult = DB::query($checkStorageCountSql);
    if (DB::fetchAssoc($checkStorageCountResult)) {
      return true;
    }

    $checkStocksSql = 'SELECT `id` '.
      'FROM `'.PREFIX.'product_on_storage` '.
      'WHERE `count` != 0 '.
      'LIMIT 1';
    $checkStocksResult = DB::query($checkStocksSql);
    if (DB::fetchAssoc($checkStocksResult)) {
      MG::setOption('showStoragesRecalculate', 'true');
      MG::setSetting('showStoragesRecalculate', 'true');
    }
    return true;
  }
}