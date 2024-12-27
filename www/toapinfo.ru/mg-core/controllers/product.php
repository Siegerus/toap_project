<?php
/**
 * Контроллер Product
 *
 * Класс Controllers_Product обрабатывает действия пользователей на странице товара.
 * - Пересчитывает стоимость товара.
 * - Подготавливает форму для вариантов товара.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Product extends BaseController {

  function __construct() {
   
    $model = new Models_Product;
    
    $id = URL::getQueryParametr('id');

    // для редиректа
    if(LANG != 'LANG' && LANG != 'default') {
      $lang = '/'.LANG;
    } else {
      $lang = '';
    }
    
    if(empty($id) && empty($_REQUEST['calcPrice']) && empty($_REQUEST['getVariantImages'])) {
      MG::response404();
      return;
    }

    if (!empty($_REQUEST['getVariantImages'])) {
      if (isset($_POST['productId'])) {
        $model->getVariantImages();
      }
      exit;
    }

    // Требуется только пересчет цены товара.
    if (!empty($_REQUEST['calcPrice'])) {
      if (isset($_POST['inCartProductId'])) {
        $model->calcPrice();
      }
      exit;
    }
    
    if (empty($product)) {
      $settings = MG::get('settings');


      $product = $model->getProduct($id);
      
      if (empty($product)) {
        MG::response404();
        return;
      } 

      // проверка на то, нужно ли показывать товар при неактивности
      if((MG::getSetting('product404') == 'true') && ($product['activity'] != 1) && (USER::access('admin_zone') != 1)) {
        MG::response404();
        return;
      }
      
      if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= strtotime($product['last_updated'])) {
        header('HTTP/1.0 304 Not Modified');
        die;
      }
      header("Last-Modified: ".gmdate("D, d M Y H:i:s", strtotime($product['last_updated']))." GMT"); 
            
      $product['currency'] = $settings['currency'];
      $blockVariants = $model->getBlockVariants($product['id'], $product['cat_id'], defined('TEMPLATE_INHERIT_FROM'));
      if ($blockVariants) {
        $variants = $model->getVariants($id, false, true);
        // оптовые цены грузим
        $product['variants'] = $variants;
        $product = MG::loadWholeSalesToCatalog(array($product));
        $product = $product[0];
        $variants = $product['variants'];
        
        if (!empty($variants)) {
          $variants = array_values($variants);
          foreach ($variants as $key => $value) {
            if($value['count'] == 0) {
              $tmp = $value;
              unset($variants[$key]);
              $variants[] = $tmp;
            }
          }
          $variants = array_values($variants);

          $firstVariant = array_shift($variants);
          $product['price'] = $firstVariant['price'];
          $product['old_price'] = $firstVariant['old_price'];
          $product['code'] = $firstVariant['code'];
          $product['count'] = $firstVariant['count'];
          $product['count_hr'] = '';
          $convertCountToHR = MG::getSetting('convertCountToHR');
          if (!empty($convertCountToHR) && !MG::isAdmin()) {
            $product['count_hr'] = MG::convertCountToHR($product['count']);
          }
          $product['variant'] = $firstVariant['id'];

          if(MG::getSetting('showMainImgVar') == 'true') {
            if($firstVariant['image'] != '') {
              $img = explode('/', $product['images_product'][0]);
              $img = end($img);
              $product['images_product'][0] = str_replace($img, $firstVariant['image'], $product['images_product'][0]);
            }
          }

          $product['weight'] = $firstVariant['weight'];
          $product['weightCalc'] = $firstVariant['weightCalc'];
          $product['price_course'] = $firstVariant['price_course'];
        }
      } else {
        $product = MG::loadWholeSalesToCatalog(array($product));
        $product = $product[0];
        // Возможно варианты товара не пришли, потому что их нет в наличии
        if (MG::getSetting('showVariantNull') !== 'true') {
          // Тогда берём варианты не зависимо от их количества
          $nulledVariants = $model->getVariants($product['id'], false, true, true);
          if ($nulledVariants) {
            $firstVariant = array_shift($nulledVariants);
            $product['price'] = $firstVariant['price'];
            $product['old_price'] = $firstVariant['old_price'];
            $product['code'] = $firstVariant['code'];
            $product['count'] = $firstVariant['count'];
            $product['count_hr'] = '';
            $convertCountToHR = MG::getSetting('convertCountToHR');
            if (!empty($convertCountToHR) && !MG::isAdmin()) {
              $product['count_hr'] = MG::convertCountToHR($product['count']);
            }
            $product['variant'] = $firstVariant['id'];
  
            if(MG::getSetting('showMainImgVar') == 'true') {
              if($firstVariant['image'] != '') {
                $img = explode('/', $product['images_product'][0]);
                $img = end($img);
                $product['images_product'][0] = str_replace($img, $firstVariant['image'], $product['images_product'][0]);
              }
            }
  
            $product['weight'] = $firstVariant['weight'];
            $product['weightCalc'] = $firstVariant['weightCalc'];
            $product['price_course'] = $firstVariant['price_course'];
          }
        }
      }

      $blockedProp = $model->noPrintProperty();      
      $propertyFormData = $model->createPropertyForm($param = array(
        'id' => $product['id'],
        'maxCount' => $product['count'],
        'productUserFields' => $product['thisUserFields'],
        'action' => "/catalog",
        'method' => "POST",
        'ajax' => true,
        'blockedProp' => $blockedProp,
        'noneAmount' => false,
        // 'noneButton' => $product['count']!=0?false:true,
        'titleBtn' => MG::getSetting('buttonBuyName'),
        'blockVariants' => $blockVariants,
        'currency_iso' => $product['currency_iso'],
        'productData' => $product,
      ), 'nope', defined('TEMPLATE_INHERIT_FROM'));      

      // Легкая форма без характеристик.   
      $liteFormData = $model->createPropertyForm($param = array(
        'id' => $product['id'],
        'maxCount' => $product['count'],
        'productUserFields' => null,
        'action' => "/catalog",
        'method' => "POST",
        'ajax' => true,
        'blockedProp' => $blockedProp,
        'noneAmount' => false,
        'noneButton' => $product['count']?false:true,
        'titleBtn' => MG::getSetting('buttonBuyName'),
        'blockVariants' => $blockVariants,
      ), 'nope', defined('TEMPLATE_INHERIT_FROM'));

      $product['price_course']+=MG::convertCustomPrice($propertyFormData['marginPrice'], $product['currency_iso'], 'set');
      $currencyRate = MG::getSetting('currencyRate');      
      $currencyShopIso = MG::getSetting('currencyShopIso');      
      $product['currency_iso'] = $product['currency_iso']?$product['currency_iso']:$currencyShopIso;
      $product['old_price'] = $product['old_price']? $product['old_price']:0;
      if (NULL_OLD_PRICE && $product['price_course'] > $product['old_price']) {
        $product['old_price'] = 0;
      }
      $product['price'] = MG::priceCourse($product['price_course']); 
      
      if (!defined('TEMPLATE_INHERIT_FROM')) {
        $product['propertyForm'] = $propertyFormData['html'];
        $product['liteFormData'] = $liteFormData['html'];
      } else {
        $product['propertyForm'] = $propertyFormData['propertyData'];
        $product['liteFormData'] = $liteFormData['propertyData'];
      }

      $product['propertyNodummy'] = $propertyFormData['propertyNodummy'];
      $product['stringsProperties'] = $propertyFormData['stringsProperties'];
      $product['filesProperties'] = $propertyFormData['filesProperties'];
      $productsImagesSubFolder = intval($product['id'] / 100) . '00';
      $productCKImagesFolder = 'product'.DS.$productsImagesSubFolder.DS.$product['id'].DS.'desc';
      $product['description'] = MG::inlineEditor(PREFIX.'product', "description", $product['id'], $product['description'], $productCKImagesFolder, null, true);
      $product['product_title'] = $product['title'];
      $product['title'] = MG::modalEditor('catalog', $product['title'], 'edit', $product["id"]);
      // Информация об отсутствии товара на складе.
      if (MG::getSetting('printRemInfo') == "true" && ($product['count']==0 || $blockVariants)) {
        // $message = 'Здравствуйте, меня интересует товар "'.str_replace("'", "&quot;", $product['title']).'" с артикулом "'.$product['code'].'", но его нет в наличии.
        // Сообщите, пожалуйста, о поступлении этого товара на склад. '; 
        $message = MG::restoreMsg('msg__product_nonavaiable2',array('#PRODUCT#' => str_replace("'", "&quot;", $product['title']), '#CODE#' => $product['code']));
        if($product['count']!=0) {
          $style = 'style="display:none;"';        
        } else {
          $style = '';
        }
        // $product['remInfo'] = "<noindex><span class='rem-info' ".$style.">Товара временно нет на складе!<br/><a rel='nofollow' href='".SITE."/feedback?message=".$message."'>Сообщить когда будет в наличии.</a></span></noindex>";
        $product['remInfo'] = "<noindex><span class='rem-info' ".$style.">".MG::restoreMsg('msg__product_nonavaiable1',array('#LINK#' => SITE."/feedback?message=".$message))."</span></noindex>";
      }
      
      if ($product['count'] < 0) {
        $product['count'] = "много";
      };
      $product['related'] = $model->createRelatedForm(array('product'=>$product['related'], 'category'=>$product['related_cat']));
    }
    
    if($seoData = Seo::getMetaByTemplate('product', $product)) {            
      foreach ($seoData as $key => $value) {
        if(!empty($value)) {
          $product[$key] = empty($product[$key]) ? preg_replace('!\s+!', ' ', htmlspecialchars($value)) : $product[$key];
        }
      }      
    }


    $product['meta_title'] = $product['meta_title'] ? $product['meta_title'] : $product['title'];
    $product['variant'] = (isset($product['variant']))?$product['variant']:'';


    // для подстановки артикулов в верстку таблицу вариантов
    $tmp1 = array();
    $tmp2 = array();
    if (!empty($product['variants'])) {
      foreach ($product['variants'] as $key => $value) {
        if($value['count'] == 0) {
          $tmp = $value;
          unset($product['variants'][$key]);
          $product['variants'][$tmp['id']] = $tmp;
        }
        $tmp1[] = 'id="variant-'.$value['id'].'"';
        $tmp2[] = 'id="variant-'.$value['id'].'" data-code="'.$value['code'].'"';
        $product['variants'][$key]['price'] = MG::numberFormat($value['price']);
        $product['variants'][$key]['price_course'] = MG::numberFormat($value['price_course']);
      }
    }
    
    if (is_string($product['propertyForm'])) {
      $product['propertyForm'] = str_replace($tmp1, $tmp2, $product['propertyForm']);
    }

    if (defined('TEMPLATE_INHERIT_FROM')) {
      $product['stringPropertiesSorted'] = Property::sortPropertyToGroup($product, true);
      $product['filesPropertiesSorted'] = Property::sortPropertyToGroup($product, true, 'filesProperties', 'layout_prop_file');
    }

    $this->data = $product;

  }

}
