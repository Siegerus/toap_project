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
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
              />
            </svg>
            <img class="foto" src="<?php echo SITE?>/uploads/ateste.png" alt="Базовый курс аналитической психологии" />
          </a>
          </div>
          <div class="assoc-plan__blockA">
            <a href="#audience">
            <div class="mc__assoc-plan_title">Кому подойдет курс</div>
            <svg
              class="icon"
              width="16"
              
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
              />
            </svg>
            <img class="foto" src="<?php echo SITE?>/uploads/kompod.png" alt="Кому подойдет курс юнгианского анализа" /></a>
          </div>
          <div class="assoc-plan__blockA">
            <a href="#rules">
            <div class="mc__assoc-plan_title">Условия</div>
            <svg
              class="icon"
              width="16"
              
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
              />
            </svg>
            <img class="foto" src="<?php echo SITE?>/uploads/yslov.png" alt="Условия обучения базового курса" /></a>
          </div>
          <div class="blockB">
            <div class="assoc-plan__blockB">
            <a href="#programs">
              <div class="mc__assoc-plan_title">Программы обучения</div>
              <svg
                class="icon"
                width="16"
                
                viewBox="0 0 16 14"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
                />
              </svg>
              <img class="foto" src="<?php echo SITE?>/uploads/progobych.png" alt="Программа обучения аналитической психологии" /></a>
            </div>
            <div class="assoc-plan__blockB">
              <a href="#price">
              <div class="mc__assoc-plan_title">Цены</div>
              <svg
                class="icon"
                width="16"
                
                viewBox="0 0 16 14"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M0.5 6H12.086L7.586 1.5L9 0.0859985L15.914 7L9 13.914L7.586 12.5L12.086 8H0.5V6Z"
                />
              </svg>
              <img class="foto" src="<?php echo SITE?>/uploads/ceni.png" alt="Цены обучения аналитической психологии" /></a>
            </div>
          </div>
        </section>
        <section class="mc__about-course " id="about">
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
              <img src="<?php echo SITE?>/uploads/ded1.png" alt="Курс для начинающих психологов" />
            </div>
            <div class="mc-w-s-cont__card" style="min-height: 220px">
              <p>
                Тем, кто хочет разобраться в анализе <br />
                Юнга и аналитической психологии
              </p>
              <img src="<?php echo SITE?>/uploads/user-2.png" alt="Курс для анализа Юнга и аналитической психологии" />
            </div>
            <div class="mc-w-s-cont__card" style="min-height: 290px">
              <p>Опытным психологам</p>
              <img src="<?php echo SITE?>/uploads/user-3.png" alt="Курс для опытных психологов" />
            </div>
          </div>
        </section>
         <section class="mc__Latest-announc activity " id="rules">
          <h2>Условия</h2>
          <div class="cards">
            <div class="cards__card">
             <p>Форма обучения:<br> очно-заочная, объем курса <b>520 часов</b>. 
                <br>Обучение состоит из лекций и практических занятий. <br><br>
                По завершении каждой темы слушатели пишут короткое эссе.
</p>
            </div>
            <div class="cards__card">
              <p>Лекции, <b>12 часов в месяц</b>, предоставляются слушателям в записи. 
                <br>Практические занятия проводятся очно и онлайн, <b>два раза в месяц по воскресеньям</b>, включают упражнения, коллоквиумы по пройденной теме, <b>со 2 семестра обучения - групповые супервизии</b>.
</p>
     
            </div>
          
            <div class="cards__card">
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
              <ul>
                <li>Социокультурная ситуация и возникновение психоанализа.</li>
                <li>Биография Юнга и возникновение аналитической психологии. Юнг и Фрейд.</li>
                <li>Дальнейшее развитие аналитической психологии.</li>
                <li>Постюнгианцы. Школы аналитической психологии: классическая, школа развития, архетипическая.</li> <li>Аналитическая психология в сравнении с другими школами и ее место в психоаналитическом пространстве.</li>
              </ul>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 217px">
              <span>02</span>
              <h3>Теоретическая модель</h3>
              <ul>
              <li>Теория личности.</li> <li>Объективная психика.</li> <li> Коллективное и личное бессознательное.</li> <li> Символы. Архетипы. Комплексы.</li> <li> Трансцендентная функция.</li> <li> Индивидуация.</li> <li> Юнгианская типология.</li>
              </ul>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 290px">
              <span>03</span>
              <h3>Теории развития</h3>
              <ul>
               <li> Психоаналитические теории развития:</li> <li> Фрейд, Кляйн, Винникот, Малер, Кохут, Бион.</li> <li> Юнгианская теория развития:</li> <li> Юнг, Нойманн, Фордхам.  </li>
              </ul>
            </div>
          </div>
          <div class="mc-t-p__cont 2">
            <div class="mc-t-p__cont-card" style="min-height: 290px">
              <span>04</span>
              <h3>Практическое применение</h3>
              <ul>
               <li> Анализ и аналитическая терапия.</li> <li> Аналитический процесс.</li> <li> Аналитическая позиция.</li> <li>Символическая функция.</li> <li> Аналитические отношения. </li> <li> Перенос/контрперенос.</li> <li> Методы в аналитической психологии: ассоциации, амплификация, интерпретация, активное воображение, анализ сновидений, работа со сказкой, песочная терапия.</li> <li> Аналитическая практика.
</li>
              </ul>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 217px">
              <span>05</span>
              <h3>Клиническое применение</h3>
              <ul>
                <li>
Диагностика. Уровни личности и защитные механизмы.</li> <li> Общие аспекты: компульсивные личности, депрессивные и маниакальные личности, нарциссические личности, параноидные и шизоидные личности, мазохистические личности, истерические личности.</li> <li> Психосоматика.</li> <li> Работа с травмой. </li> <li>Работа с зависимостями. </li>
              </ul>
            </div>
            <div class="mc-t-p__cont-card" style="min-height: 290px">
              <span>06</span>
              <h3>Организация</h3>
              <ul>
                <li>Огранизация  юнгианского сообщества.</li> <li> Система подготовки аналитиков.</li> <li> Аналитическая психология в России.</li> <li> Организация собственной практики.</li> <li> Юридические аспекты.</li> <li> Возможности развития.</li>
              </ul>
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

    echo '<div class="mc-p__cont-card" >
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

  
 