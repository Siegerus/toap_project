<?php
mgSEO($data);
//viewData($data['newProducts']);
?>
    <div class="main__cont mc max-cont-width">
  
        <section class="mc__title">
          <h1>Развиваем аналитическую <br />психологию и психоанализ</h1>
          <a class="main-btn" href="<?php echo SITE?>/obuchenie">Выбрать обучение</a>
        </section>

        <!-- <section class="mc__assoc-plan"> -->
          <?php component('catalog/categories',0); ?>
        <!-- </section> -->
        <!-- <section class="mc__about"> -->
          <?php if ($cd = str_replace("&nbsp;", "", $data['cat_desc'])): ?>
              <?php if ($data['cat_img']): ?>
              <img class=""
                  src="<?php echo SITE . $data['cat_img'] ?>"
                  alt="<?php echo $data['seo_alt'] ?>"
                  title="<?php echo $data['seo_title'] ?>">
              <?php endif; ?>
                  <?php echo $data['cat_desc'] ?>
          <?php endif; ?>
      
        <!-- </section> -->
       <!--  <section class="mc__Latest-announc">
          <h2>Направления деятельности:</h2>
          <div class="cards">
            <div class="cards__card">
              <img src="https://toap.klarmach.ru/uploads/prov poc 2.png" alt="" />
              <p>Проводим показательные приемы пациентов</p>
              <span>02.07.24</span>
            </div>
            <div class="cards__card">
              <img src="https://toap.klarmach.ru/uploads/prov poc 2.png" alt="" />
              <p>Проводим показательные приемы пациентов</p>
              <span>02.07.24</span>
            </div>
            <div class="cards__card">
              <img src="https://toap.klarmach.ru/uploads/prov poc 2.png" alt="" />
              <p>Проводим показательные приемы пациентов</p>
              <span>02.07.24</span>
            </div>
            <div class="cards__card">
              <img src="https://toap.klarmach.ru/uploads/prov poc 2.png" alt="" />
              <p>Проводим показательные приемы пациентов</p>
              <span>02.07.24</span>
            </div>
          </div>
        </section> -->
         <section class="mc__Latest-announc activity">
          <h2>Направления деятельности:</h2>
          <div class="cards">
            <div class="cards__card">
              <img src="<?php echo PATH_SITE_TEMPLATE?>/images/icons/1.png" alt="" />
              <p>Знакомство широкой аудитории с идеями Юнга, с методом аналитической психологии, практикой и возможностями юнгианского анализа</p>
            </div>
            <div class="cards__card">
              <img src="<?php echo PATH_SITE_TEMPLATE?>/images/icons/2.png" alt="" />
              <p>Подготовка, обучение и профессиональное развитие аналитических психологов/юнгианских аналитиков</p>
     
            </div>
            <div class="cards__card">
              <img src="<?php echo PATH_SITE_TEMPLATE?>/images/icons/3.png" alt="" />
              <p>Исследования в области аналитической психологии, в первую очередь практического применения подходов и методов аналитической психологии.</p>
            </div>
            <div class="cards__card">
              <img src="<?php echo PATH_SITE_TEMPLATE?>/images/icons/4.png" alt="" />
              <p>Расширение профессионального сообщества  как пространства для плодотворной совместной работы в поле аналитической психологии. </p>
            </div>
          </div>
          <p class="activity-gain">Мы уверены, что сообщество единомышленников - лучшая возможность для индивидуального и профессионального развития.</p>
        </section>
     
        <!-- <section class="mc__news"> -->
          [news-anons count="6"]
        <!-- </section> -->
     
      </div>