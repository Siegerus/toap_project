<!--
Доступны переменные:
  $pluginName - название плагина
  $lang - массив фраз для выбранной локали движка
  $options - набор данного плагина хранимый в записи таблиц mg_setting
  $entity - набор записей сущностей плагина из его таблицы
  $pagination - блок навигациицам 
-->

<div class="section-<?php echo $pluginName ?>"><!-- $pluginName - задает название секции для разграничения JS скрипта -->

  <!-- Тут начинается Верстка модального окна -->


  <div class="reveal-overlay" style="display:none;">
      <div class="reveal xssmall" id="add-plug-modal" style="display:block;">
        <button class="close-button closeModal" type="button"><i class="fa fa-times-circle-o" aria-hidden="true"></i></button>
        <div class="reveal-header">
          <h4 class="pages-table-icon" id="modalTitle"><i class="fa fa-plus-circle" aria-hidden="true"></i> <?php echo $lang['HEADER_MODAL_ADD']; ?></h4>
        </div>
        <div class="reveal-body">

          <div class="widget-body slide-editor"><!-- Содержимое окна, управляющие элементы -->
            <div class="block-for-form" >
              <ul class="custom-form-wrapper fields-calback">
                <li>
                  <span><?php echo $lang['RING_SETTINGS_NAME'] ?> </span> <input type="text" name="name" value=""/>
                </li>
                <li>
                  <span><?php echo $lang['RING_SETTINGS_PHONE'] ?></span> <input type="text" name="phone" value=""/>
                </li>
                <li>
                  <span><?php echo $lang['RING_SETTINGS_CITY'] ?></span> <input type="text" name="city_id" value=""/>
                </li>
                <li>
                  <span><?php echo $lang['RING_SETTINGS_TARGET_R'] ?></span>
                  <select name="mission" class="medium">
                    <?php echo $selectMissions;?>
                  </select>
                </li>
                <li>
                  <span><?php echo $lang['RING_SETTINGS_DATE'] ?></span> <input type="text" name="date_callback" value=""/>
                </li>
                <li>
                  <span><?php echo $lang['RING_SETTINGS_TIME'] ?></span>
                  <?php echo $lang['RING_SETTINGS_FROM'] ?>
                  <select name="from" class="small">
                    <?php echo $selectHour;?>
                  </select>
                  <?php echo $lang['RING_SETTINGS_TO'] ?>
                  <select name="to" class="small">
                   <?php echo $selectHour;?>
                  </select>
                </li>
                <li>
                  <span><?php echo $lang['RING_SETTINGS_STATUS'] ?></span>
                  <select name="status_id" class="medium">
                    <?php foreach($status as $id => $item){
                      echo "<option value='".$id."'>".$item."</option>";
                    }?>
                  </select>
                </li>
                <li>
                  <span class="textarea-text" style="vertical-align: top;"><?php echo $lang['RING_SETTINGS_COMMENT'] ?> </span>
                  <textarea name="comment">  </textarea>
                </li>
              </ul>
            </div>
          </div>

        </div>
        <div class="reveal-footer clearfix">
        <a class="button success fl-right save-button" href="javascript:void(0);"><i class="fa fa-floppy-o" aria-hidden="true"></i> <?php echo $lang['SAVE_MODAL'] ?></a>
      </div>
    </div>
  </div>






  <!-- Тут заканчивается Верстка модального окна -->

  <!-- Тут начинается верстка видимой части станицы настроек плагина-->
  <div class="widget-body">

     <div class="widget-panel">
       <a role="button" href="javascript:void(0);" class="show-filters tool-tip-top button plugin-padding" title="<?php echo $lang['T_TIP_SHOW_FILTER'];?>"><span><i class="fa fa-filter" aria-hidden="true"></i> <?php echo $lang['FILTER'];?></span></a>
        <a role="button" href="javascript:void(0);" class="show-property-order tool-tip-top button info" title="<?php echo $lang['T_TIP_SHOW_PROPERTY'];?>"><span><i class="fa fa-cogs" aria-hidden="true"></i> <?php echo $lang['SHOW_PROPERTY'];?></span></a>

        <div class="filter fl-right">
          <span class="last-items"><?php echo $lang['SHOW_COUNT_ORDER'];?></span>
          <select class="last-items-dropdown countPrintRowsEntity small">
            <?php
            foreach(array(10, 20, 30, 50, 100) as $value){
              $selected = '';
              if($value == $countPrintRowsBackRing){
                $selected = 'selected="selected"';
              }
              echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
            }
            ?>
          </select>
        </div>
        <div class="clear"></div>
      </div>

      <div class="filter-container widget-panel-content" style="display: none;"<?php if($displayFilter){echo "style='display:block'";} ?>>
        <?php echo $filter ?>

        <div class="clear"></div>
      </div>

      <div class="property-order-container widget-panel-content" style="display: none;">
        <h2><?php echo $lang['RING_SETTINGS_FORM_AREAS'] ?>:</h2>
          <form  class="base-setting" name="base-setting" method="POST">
              <ul class="list-option">
                  
                  <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_NAME'] ?>:</span> <input type="checkbox" name="name" value="<?php echo $options["name"]?>" <?php echo ($options["name"]&&$options["name"]!='false')?'checked=cheked':''?>></label></li>
                  <?php /* <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_CITY'] ?>:</span> <input type="checkbox" name="city" value="<?php echo $options["city"]?>" <?php echo ($options["city"]&&$options["city"]!='false')?'checked=cheked':''?>></label></li>  */?>
                  <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_COMMENT'] ?>:</span> <input type="checkbox" name="comment" value="<?php echo $options["comment"]?>" <?php echo ($options["comment"]&&$options["comment"]!='false')?'checked=cheked':''?>></label></li>
                  <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_RING'] ?>:</span> <input type="checkbox" name="period" value="<?php echo $options["period"]?>" <?php echo ($options["period"]&&$options["period"]!='false')?'checked=cheked':''?>></label></li>
                  <?php /* <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_TARGET'] ?>:</span> <input type="checkbox" name="mission" value="<?php echo $options["mission"]?>" <?php echo ($options["mission"]&&$options["mission"]!='false')?'checked=cheked':''?>></label></li>  */?>
                  <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_CAPCHA'] ?>:</span> <input type="checkbox" name="caphpa" value="<?php echo $options["caphpa"]?>" <?php echo ($options["caphpa"]&&$options["caphpa"]!='false')?'checked=cheked':''?>></label></li>
                  <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_PERIOD'] ?>:</span> <input type="checkbox" name="period" value="<?php echo $options["period"]?>" <?php echo ($options["period"]&&$options["period"]!='false')?'checked=cheked':''?>></label></li>
                  <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_DATE'] ?>:</span> <input type="checkbox" name="date" value="<?php echo $options["date"]?>" <?php echo ($options["date"]&&$options["date"]!='false')?'checked=cheked':''?>></label></li>
                  <li><label class="one-line"><span><?php echo $lang['RING_SETTINGS_AGREE'] ?>:</span> <input type="checkbox" name="agreement" value="<?php echo $options["agreement"]?>" <?php echo ($options["agreement"]&&$options["agreement"]!='false')?'checked=cheked':''?>></label></li>

                  <li><span><?php echo $lang['RING_SETTINGS_AVAILABLE_PERIOD'] ?>:</span> <?php echo $lang['RING_SETTINGS_FROM'] ?>
                    <select name="from" class="small">
                       <?php
                       $hours = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24');
                        foreach ($hours as $value) {
                          $selected = '';
                          if($value == $options["from"]){
                            $selected = 'selected="selected"';
                          }
                          echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
                        }
                        ?>
                    </select>
                    <?php echo $lang['RING_SETTINGS_TIME_TO'] ?>
                    <select name="to" class="small">
                       <?php
                       $hours = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24');
                        foreach ($hours as $value) {
                          $selected = '';
                          if($value == $options["to"]){
                            $selected = 'selected="selected"';
                          }
                          echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
                        }
                        ?>
                    </select>
                  </li>
                  <span><?php echo $lang['RING_SETTINGS_PREFIX'] ?>: </span> <input style="vertical-align: top;" type="text" name="phone_prefix" value="<?php echo $options["phone_prefix"]?>"/></li>
                  <?php /* <li><span class="textarea-text"><?php echo $lang['RING_SETTINGS_CITIES_LIST'] ?>:</span><textarea type="text" name="city_list"><?php echo $options["city_list"]?></textarea></li> */?>
                  <?php /* <li><span class="textarea-text"><?php echo $lang['RING_SETTINGS_TARGET_LIST'] ?>:</span><textarea type="text" name="mission_list"><?php echo $options["mission_list"]?></textarea></li> */?>
				  <li><span><?php echo $lang['RING_SETTINGS_MAIL'] ?></span> <input style="vertical-align: top;" type="text" name="email" value="<?php echo $options["email"]?>"/></li>
                  <li><?php echo $lang['RING_SETTINGS_CONDITION_MAIL'] ?></li>
              </ul>
              <div class="clear"></div>
          </form>
          <div class="clear"></div>
        <a role="button" href="javascript:void(0);" class="base-setting-save custom-btn button success"><span><i class="fa fa-floppy-o" aria-hidden="true"></i><?php echo $lang['RING_SETTINGS_SAVE'] ?> </span></a>
        <div class="clear"></div>
      </div>
    <div class="wrapper-entity-setting">


      <div class="clear"></div>
      <!-- Тут начинается верстка таблицы сущностей  -->
      <div class="entity-table-wrap">
        <div class="clear"></div>
        <div class="entity-settings-table-wrapper">
          <table class="widget-table main-table">
            <thead>
            <tr>
              <th class="id-width">№</th>
              <th>
               <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="add_datetime") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="add_datetime") ? $sorterData[1]*(-1) : 1 ?>" data-field="add_datetime"><?php echo $lang['RING_SETTINGS_ADDED'] ?></a>
              </th>
              <th>
               <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="name") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="name") ? $sorterData[1]*(-1) : 1 ?>" data-field="name"><?php echo $lang['RING_SETTINGS_NAME'] ?></a>
              </th>
              <th>
               <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="phone") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="phone") ? $sorterData[1]*(-1) : 1 ?>" data-field="phone"><?php echo $lang['RING_SETTINGS_PHONE'] ?></a>
              </th>
              <th>
               <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="city_id") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="city_id") ? $sorterData[1]*(-1) : 1 ?>" data-field="city_id"><?php echo $lang['RING_SETTINGS_CITY'] ?></a>
              </th>
              <th>
                 <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="mission") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="mission") ? $sorterData[1]*(-1) : 1 ?>" data-field="mission"><?php echo $lang['RING_SETTINGS_TARGET_R'] ?></a>
              </th>
              <th>
                 <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="date_callback") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="date_callback") ? $sorterData[1]*(-1) : 1 ?>" data-field="date_callback"><?php echo $lang['RING_SETTINGS_DATE_R'] ?></a>
              </th>
               <th>
                 <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="time_callback") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="time_callback") ? $sorterData[1]*(-1) : 1 ?>" data-field="time_callback"><?php echo $lang['RING_SETTINGS_TIME_R'] ?></a>
              </th>
               <th>
                 <a role="button" href="javascript:void(0);" class="order <?php echo ($sorterData[0]=="status_id") ? 'field-sorter '.$sorterData[3]:'field-sorter asc' ?>" data-sort="<?php echo ($sorterData[0]=="status_id") ? $sorterData[1]*(-1) : 1 ?>" data-field="status_id"><?php echo $lang['RING_SETTINGS_STATUS'] ?>Статус</a>
              </th>
              <th class="actions"><?php echo $lang['ACTIONS'];?>
              </th>
            </tr>
          </thead>
            <tbody class="entity-table-tbody">
              <?php
              setlocale(LC_ALL, 'ru_RU', 'rus_RUS', 'Russian_Russia');
              if (empty($entity)): ?>
                <tr class="no-results">
                  <td colspan="10" style="text-align: center;" align="center"><?php echo $lang['ENTITY_NONE']; ?></td>
                </tr>
                  <?php else: ?>
                    <?php foreach ($entity as $row): ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                      <td><?php echo $row['id']; ?></td>

                      <td class="add_datetime">
                        <?php echo MG::dateConvert($row['add_datetime']).' ['.date('H:i',strtotime($row['add_datetime'])).']'; ?>
                      </td>
                      <td class="name">
                        <?php echo $row['name'] ?>
                      </td>

                      <td class="phone">
                        <?php echo $row['phone'] ?>
                      </td>

                      <td class="city_id">
                        <?php echo $row['city_id'] ?>
                      </td>

                      <td class="mission">
                        <?php echo $row['mission'] ?>
                      </td>

                      <td class="date_callback">
                        <?php if($row['date_callback']!='0000-00-00'){echo MG::dateConvert($row['date_callback']);} ?>
                      </td>

                      <td class="time_callback">
                        <?php echo $row['time_callback'] ?>
                      </td>

                      <td class="status_id">
                        <?php
                        $class = 'get-paid';
                        if($row['status_id'] == 1){
                         $class = 'get-paid';
                        }
                        if($row['status_id'] == 2){
                         $class = 'dont-paid';
                        }
                        if($row['status_id'] == 3){
                         $class = 'activity-product-true';
                        }
                        echo "<span class='".$class."'> ".$status[$row['status_id']]."</span>";
                        ?>
                      </td>

                      <td class="actions">
                        <ul class="action-list"><!-- Действия над записями плагина -->
                          <li class="edit-row"
                              data-id="<?php echo $row['id'] ?>"
                              data-type="<?php echo $row['type']; ?>">
                            <a class="tool-tip-bottom fa fa-pencil" href="javascript:void(0);"
                               title="<?php echo $lang['EDIT']; ?>"></a>
                          </li>
                          <li class="visible tool-tip-bottom  <?php echo ($row['invisible']) ? 'active' : '' ?>"
                              data-id="<?php echo $row['id'] ?>"
                              title="<?php echo ($row['invisible']) ? $lang['ACT_V_ENTITY'] : $lang['ACT_UNV_ENTITY']; ?>">
                            <a class="fa fa-lightbulb-o <?php echo ($row['invisible']) ? 'active' : '' ?>" href="javascript:void(0);"></a>
                          </li>
                          <li class="delete-row"
                              data-id="<?php echo $row['id'] ?>">
                            <a class="tool-tip-bottom fa fa-trash" href="javascript:void(0);"
                               title="<?php echo $lang['DELETE']; ?>"></a>
                          </li>
                        </ul>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="table-pagination clear" style="margin: 10px;padding-bottom: 40px;">
      <?php echo $pagination ?>  <!-- Вывод навигации -->
      </div>
      <div class="clear"></div>
    </div>
  </div>

  <script>
     $(".section-back-ring  #add-plug-modal .fields-calback input[name=city_id]").autocomplete({
          source: availableTags
     });

     $( ".ui-autocomplete" ).css('z-index','1000');
     $.datepicker.regional['ru'] = {
       closeText: 'Закрыть',
       prevText: '&#x3c;Пред',
       nextText: 'След&#x3e;',
       currentText: 'Сегодня',
       monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
         'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
       monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
         'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
       dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
       dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
       dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
       dateFormat: 'dd.mm.yy',
       firstDay: 1,
       isRTL: false
     };
     $.datepicker.setDefaults($.datepicker.regional['ru']);
     $('.section-back-ring  #add-plug-modal .fields-calback input[name="date_callback"]').datepicker({ dateFormat: "yy-mm-dd" });

     if(typeof $.mask != 'undefined') {
      $(".section-back-ring  #add-plug-modal .fields-calback input[name=phone]").mask("+7 (999) 999-99-99");
     }
  </script>