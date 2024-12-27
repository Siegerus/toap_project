<?php
mgSEO($data);
?>
<?php mgAddMeta('components/catalog/item/item.css'); ?>
<?php mgAddMeta('components/product/variants/variants.js'); ?>
<?php mgAddMeta('components/product/product.js'); 
mgAddMeta('components/cart/btn/add/add.js');
?>

  <!-- catalog - start -->
    <div class="l-row l-base-kurs">
<!-- =================================================================================== -->
<div class="main__cont mc max-cont-width">
        <section class="mc__title">
          <h1><?php echo $data['titleCategory']?></h1>
        
        </section>
        <section class="mc__assoc-plan">
          <div class="assoc-plan__blockA">
            <a href="#about">
            <div class="mc__assoc-plan_title">О курсе</div>
            <svg
              class="icon"
              width="16"
              min-height="14"
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
              />
            </svg>
            <img class="foto" src="<?php echo SITE?>/uploads/ateste.png" alt="" />
          </a>
          </div>
          <div class="assoc-plan__blockA">
            <a href="#audience">
            <div class="mc__assoc-plan_title">Кому подойдет курс</div>
            <svg
              class="icon"
              width="16"
              min-height="14"
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
              />
            </svg>
            <img class="foto" src="<?php echo SITE?>/uploads/kompod.png" alt="" /></a>
          </div>
          <div class="assoc-plan__blockA">
            <a href="#rules">
            <div class="mc__assoc-plan_title">Условия</div>
            <svg
              class="icon"
              width="16"
              min-height="14"
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
              />
            </svg>
            <img class="foto" src="<?php echo SITE?>/uploads/yslov.png" alt="" /></a>
          </div>
          <div class="">
            <a href="#programs">
            <div class="assoc-plan__blockB">
              <div class="mc__assoc-plan_title">Программы обучения</div>
              <svg
                class="icon"
                width="16"
                min-height="14"
                viewBox="0 0 16 14"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
                />
              </svg>
              <img class="foto" src="<?php echo SITE?>/uploads/progobych.png" alt="" /></a>
            </div>
            <div class="assoc-plan__blockB">
              <a href="#price">
              <div class="mc__assoc-plan_title">Цены</div>
              <svg
                class="icon"
                width="16"
                min-height="14"
                viewBox="0 0 16 14"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
                />
              </svg>
              <img class="foto" src="<?php echo SITE?>/uploads/ceni.png" alt="" /></a>
            </div>
          </div>
        </section>
        <section class="mc__about-course" id="about">
          <h2 class="sect-title">О курсе</h2>
     <?php echo $data['cat_desc']?>
          
        </section>
        <section class="mc__who-suitable" id="audience">
          <h2 class="sect-title">Кому подойдет курс</h2>
          <div class="mc-w-s-cont">
            <div class="mc-w-s-cont__card" style="min-height: 290px">
              <p>
                Начинающим психологам и <br />
                психоаналитикам, врачам, cтудентам
              </p>
              <img src="<?php echo SITE?>/uploads/ded1.png" alt="" />
            </div>
            <div class="mc-w-s-cont__card" style="min-height: 220px">
              <p>
                Тем, кто хочет разобраться в анализе <br />
                Юнга и аналитической психологии
              </p>
              <img src="<?php echo SITE?>/uploads/user-2.png" alt="" />
            </div>
            <div class="mc-w-s-cont__card" style="min-height: 290px">
              <p>Опытным психологам</p>
              <img src="<?php echo SITE?>/uploads/user-3.png" alt="" />
            </div>
          </div>
        </section>
         <section class="mc__Latest-announc activity " id="rules">
          <h2>Условия</h2>
          <div class="cards">
            <div class="cards__card">
              <img src="<?php echo PATH_SITE_TEMPLATE?>/images/icons/1-1.png" alt="" />
              <p>Форма обучения:<br> очно-заочная, объем курса <b>520 часов</b>. 
                <br>Обучение состоит из лекций и практических занятий. <br><br>
                По завершении каждой темы слушатели пишут короткое эссе.
</p>
            </div>
            <div class="cards__card">
              <img src="<?php echo PATH_SITE_TEMPLATE?>/images/icons/1-2.png" alt="" />
              <p>Лекции, <b>12 часов в месяц</b>, предоставляются слушателям в записи. 
                <br>Практические занятия проводятся очно и онлайн, <b>два раза в месяц по воскресеньям</b>, включают упражнения, коллоквиумы по пройденной теме, <b>со 2 семестра обучения - групповые супервизии</b>.
</p>
     
            </div>
          
            <div class="cards__card">
              <img src="<?php echo PATH_SITE_TEMPLATE?>/images/icons/1-3.png" alt="" />
              <p>По окончании курса слушатели пишут квалификационную работу - <b>описание клинического случая</b> (предпочтительно) либо исследование теоретического концепта (для тех, у кого нет собственной практики). 
</p>
            </div>
          </div>
         
        </section>
        <section class="mc__training-program" id="programs">
          <h2 class="sect-title">Программа обучения</h2>
          <div class="mc-t-p__cont one">
            <div class="mc-t-p__cont-card" style="min-height: 290px">
              <span>01</span>
              <h3>История</h3>
              <p>
                Социокультурная ситуация и возникновение психоанализа.<br> Биография Юнга и возникновение аналитической психологии. Юнг и Фрейд. Дальнейшее развитие аналитической психологии.<br> Постюнгианцы. Школы аналитической психологии: классическая, школа развития, архетипическая. <br>Аналитическая психология в сравнении с другими школами и ее место в психоаналитическом пространстве.
              </p>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 217px">
              <span>02</span>
              <h3>Теоретическая модель</h3>
              <p>
              Теория личности.<br> Объективная психика.<br> Коллективное и личное бессознательное.<br> Символы.<br> Архетипы.<br> Комплексы.<br> Трансцендентная функция.<br> Индивидуация.<br> Юнгианская типология.
              </p>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 290px">
              <span>03</span>
              <h3>Теории развития</h3>
              <p>
                Психоаналитические теории развития:<br> Фрейд, Кляйн, Винникот, Малер, Кохут, Бион.<br> Юнгианская теория развития:<br> Юнг,  Нойманн, Фордхам.  
              </p>
            </div>
          </div>
          <div class="mc-t-p__cont 2">
            <div class="mc-t-p__cont-card" style="min-height: 290px">
              <span>04</span>
              <h3>Практическое применение</h3>
              <p>
                Анализ и аналитическая терапия.<br> Аналитический процесс.<br> Аналитическая позиция.<br> Символическая функция.<br> Аналитические отношения. <br> Перенос/контрперенос.<br> Методы в аналитической психологии: ассоциации, амплификация, интерпретация, активное воображение, анализ сновидений, работа со сказкой, песочная терапия.<br> Аналитическая практика.

              </p>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 217px">
              <span>05</span>
              <h3>Клиническое применение</h3>
              <p>
Диагностика.<br> Уровни личности и защитные механизмы.<br> Общие аспекты: компульсивные личности, депрессивные и маниакальные личности, нарциссические личности, параноидные и шизоидные личности, мазохистические личности, истерические личности.<br> Психосоматика.<br> Работа с травмой. <br>Работа с зависимостями. 
              </p>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 290px">
              <span>06</span>
              <h3>Организация</h3>
              <p>
                Огранизация  юнгианского сообщества.<br> Система подготовки аналитиков.<br> Аналитическая психология в России.<br> Организация собственной практики.<br> Юридические аспекты.<br> Возможности развития.
              </p>
            </div>
          </div>
        </section>
        <section class="mc__prices" id="price">
          <h2 class="sect-title">Цены</h2>
          <div class="mc-p__cont">
          <?php
foreach ($data['items'] as $item) {
    $itemId = htmlspecialchars($item['id']);
    $itemCount = htmlspecialchars($item['count']);
    $itemVariants = htmlspecialchars($item['variants']);
    $itemPrice = $item['real_price'];
    $itemCurrency = htmlspecialchars($item['currency']);
    $itemDescription = nl2br($item['description']);
    $itemImageUrl = 'https://toapinfo.ru/uploads/' . htmlspecialchars($item['image_url']);
    $itemUrl = htmlspecialchars($item['url']);

    echo '<div class="mc-p__cont-card" href="' . $itemUrl . '">
            <div class="mc-p__cont-card_txt">
                <p>' . MG::numberFormat($itemPrice) . ' ' . $itemCurrency . '</p>
                <span>' . $itemDescription . '</span>
            </div>
           
             ' . '[buy-click id="' . $itemId . '" count="' . $itemCount . '" variant="' . $itemVariants . '"]' . '
            <img src="' . $itemImageUrl . '" alt="" />
            <a href="' . $itemUrl . '" class="card-link"></a>
          </div>';
}
?>   
          </div>
        </section>
  

    </div>
<!-- =================================================================================== -->
    </div>
    <!-- catalog - end -->

  
 