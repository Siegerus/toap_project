<?php
/**
 *  Файл представления Catalog - выводит сгенерированную движком информацию на странице сайта с каталогом товаров.
 *  В этом  файле доступны следующие данные:
 *   <code>
 *    $data['items'] => Массив товаров,
 *    $data['titleCategory'] => Название открытой категории,
 *    $data['cat_desc'] => Описание открытой категории,
 *    $data['pager'] => html верстка  для навигации страниц,
 *    $data['searchData'] => Результат поисковой выдачи,
 *    $data['meta_title'] => Значение meta тега для страницы,
 *    $data['meta_keywords'] => Значение meta_keywords тега для страницы,
 *    $data['meta_desc'] => Значение meta_desc тега для страницы,
 *    $data['currency'] => Текущая валюта магазина,
 *    $data['actionButton'] => Тип кнопки в мини карточке товара,
 *    $data['cat_desc_seo'] => SEO описание каталога,
 *    $data['seo_alt'] => Алтернативное подпись изображение категории,
 *    $data['seo_title'] => Title изображения категории
 *   </code>
 *
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php viewData($data['items']); ?>
 *   </code>
 *
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php echo $data['items']; ?>
 *   </code>
 *
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложную программную логику логику.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Views
 */
// Установка значений в метатеги title, keywords, description.
mgSEO($data);

?>
<?php mgAddMeta('components/catalog/item/item.css'); ?>
<?php mgAddMeta('components/product/variants/variants.js'); ?>
<?php mgAddMeta('components/product/product.js'); 
mgAddMeta('components/cart/btn/add/add.js');
?>
<?php if (URL::isSection('basic_course') ) { 
  layout('basic_course', $data);
} else { ?>
    <div class="l-row">
      <div class="l-col min-0--12 ">
      <div class="main__cont mc max-cont-width">
        <section class="mc__title">
          <h1><?php echo $data['titleCategory']?></h1>
        </section>

       <?php component('catalog/categories',$data['id']); ?>
        <!-- </section> -->
        <!-- <section class="mc__about"> -->
          <?php if ($cd = str_replace("&nbsp;", "", $data['cat_desc'])): ?>
              <?php if ($data['cat_img']): ?>
              <img class=""
                  src="<?php echo SITE . $data['cat_img'] ?>"
                  alt="<?php echo $data['seo_alt'] ? $data['seo_alt'] :  $item['titleCategory'] ?>"
                  title="<?php echo $data['seo_title'] ? $data['seo_title'] :  $data['titleCategory']?>">
              <?php endif; ?>
                  <?php echo $data['cat_desc'] ?>
          <?php endif; ?>

          <section class="mc__assoc-plan menu-cats-main">
     <?php foreach ($data['items'] as $key => $item):  ?>
          <a class="assoc-plan__blockA menu-cats" href="<?php echo $item['link']?>">
                        <p><?php echo $item['title'] ?></p>
                        <svg class="icon" width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"></path>
                        </svg>
                         <img class="foto" src="<?php echo SITE.'/uploads/'.$item['image_url']?>" alt="<?php echo $item['seo_alt'] ? $item['seo_alt'] :  $item['title'] ?>" title="<?php echo $item['seo_title'] ? $item['seo_title'] :  $item['title'] ?>">
                                            </a> 
        <?php endforeach ?>                                
    </section>
      
    </div>
</div>
</div>
  <?php }
