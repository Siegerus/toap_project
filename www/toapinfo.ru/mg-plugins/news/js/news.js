/**
 * Модуль для  раздела "Новости".
 */

$(".ui-autocomplete").css('z-index', '1000');
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

var news = (function () {
  return {
   
    lang: [], // локаль плагина новостей
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function() {
      // установка локали плагина новостей
      admin.ajaxRequest(
        {
          mguniqueurl: "action/seLocalesToPlug",
          pluginName: 'news'
        },
        function(response) {
         news.lang = response.data;
        }
      );

      // Вызов модального окна при нажатии на кнопку добавления новости.
      $('.admin-center').on('click', '.section-news .add-new-button', function(){
        news.openModalWindow('add');
      });


      // Вызов модального окна при нажатии на кнопку изменения новости.
      $('.admin-center').on('click', '.section-news .edit-row', function(){
        news.openModalWindow('edit', $(this).attr('id'));
      });

      // Удаление новости.
      $('.admin-center').on('click', '.section-news .delete-order', function(){
        news.deleteNews($(this).attr('id'));
      });

      // Сохранение новости при на жатии на кнопку сохранить в модальном окне.
      $('body').on('click', '#add-news-wrapper .save-button', function(){
        news.saveNews($(this).attr('id'));
      });

       // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('change', '#photoimg', function(){
        if($(this).val()){
          news.addImageToNews();
        }
      });

      //ckeditor
      $('.admin-center').on('click', '#add-news-wrapper .accordion-title', function () {
        const id = $('#add-news-wrapper .save-button').attr('id');
        const site = window.location.host;
        $('textarea[name=html_content]').ckeditor({
          filebrowserUploadUrl: site+'/ajax?mguniqueurl=action/upload&upload_dir=news/'+id
        });
      });

      // Удаляение изображения новости, как из БД таи физически с сервера.
      $('body').on('click', '.cancel-img-upload', function(){
        news.delImageNews($(this).attr('id'),$('.prev-img img').attr('src'));
      });


      // Устанавливает количиство выводимых записей в этом разделе.
      $('.admin-center').on('change', '.section-news .countPrintRowsPage', function(){

        var count = $(this).val();
        admin.ajaxRequest({
          pluginHandler: 'news', // имя папки в которой лежит данный плагин
          actionerClass: "News", // класс News в news.php - в папке плагина
          action: "setCountPrintRowsNews", // название действия в пользовательском  классе News
          count: count
        },
        function(response) {
          admin.refreshPanel();
        }
        );

      });


      // Сохранение продукта при нажатии на кнопку сохранить в модальном окне.
      $('body').on('click', '.previewPage', function(){
        news.previewPage();
      });

      // Открывает seo-блок
      $('body').on('click', '.seo-title', function (){
        $('.seo-wrapper').css('display', 'block');
      });

      // нажатие на кнопку генерации метатегов
      $('#add-news-wrapper').on('click', '#add-plug-modal .seo-gen-tmpl', function () {
        news.generateSeoFromTmpl();
      });
    },


    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     */
    openModalWindow: function(type, id) {  
      switch (type) {
        case 'edit':{
          news.clearFileds();
          $('#modalTitle').text('Редактирование новости');
          news.editPage(id);
          break;
        }
        case 'add':{
          $('#modalTitle').text('Создание новости');
          news.clearFileds();
          break;
        }
        default:{
          news.clearFileds();
          break;
        }
      }

      // Вызов модального окна.
      admin.openModal($('#add-plug-modal'));
      
    },


    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function() {
      $('.errorField').css('display','none');

      var error = false;

      // наименование не должно иметь специальных символов.
      if(!admin.regTest(1,$('input[name=title]').val()) || !$('input[name=title]').val()){
        $('input[name=title]').parent("label").find('.errorField').css('display','block');
        error = true;
      }

      // url обязательно надо заполнить.
      if(!$('input[name=url]').val()){
        $('input[name=url]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
      if(!$('input[name=author]').val()){
        $('input[name=author]').parent("label").find('.errorField').css('display','block');
        error = true;
      }

      if(error == true){
        return false;
      }

      return true;
    },


    /**
     * Сохранение изменений в модальном окне добавления новости.
     * Используется и для сохранения редактированных данных и для сохраниеня новости.
     * id - идентификатор новости, может отсутсвовать если производится добавление новости.
     */
    saveNews: function(id) {

      // Если поля не верно заполнены, то не отправляем запрос на сервер.
      if(!news.checkRulesForm()){
        return false;
      }

      $time = '';
      if($('input[name=add_date]').val() != '01.01.1970') {
        $time = $('input[name=add_date]').val();
      }
 
      // Пакет характеристик новости.
      var packedProperty = {
        pluginHandler: 'news', // имя папки в которой лежит данный плагин
        actionerClass: "News", // класс News в news.php - в папке плагина
        action: "saveNews", // название действия в пользовательском  классе News
        id: id,
        title: $('input[name=title]').val(),
        url: $('input[name=url]').val(),
        author: $('input[name=author]').val(),
        image_url: $('.product-text-inputs input[name=photoimg]').val()?$('.product-text-inputs input[name=photoimg]').val():$('.prev-img img').attr('src'),
        description: $('textarea[name=html_content]').val(),
        meta_title: $('input[name=meta_title]').val(),
        meta_keywords: $('input[name=meta_keywords]').val(),
        meta_desc: $('textarea[name=meta_desc]').val(),
        add_date: $time,
      }

      // отправка данных на сервер для сохранения
      admin.ajaxRequest(
        packedProperty,
        function(response) {
          admin.indication(response.status, response.msg);

           // получаем URL имеющейся картинки товара, если она была
          var src=$('tr[id='+response.data.id+'] .image_url .uploads').attr('src');
          if(response.data.image_url){
            // если идет процесс обновления и картинка новая то обновляем путь к ней
            src=admin.SITE+'/uploads/news/'+response.data.image_url;
          }else {
            src=admin.SITE+'/mg-admin/design/images/no-img.png'
          }

          if(response.data.image_url=='no-img.png') {
            src=admin.SITE+'/mg-admin/design/images/no-img.png'
          }
          // html верстка для  записи в таблице раздела
          var row='\
            <tr id="'+response.data.id+'">\
              <td class="product-picture image_url">\
                <img class="uploads" src="'+src+'"/>\
              </td>\
              <td class="title">'+response.data.title+'</td>\
              <td class="url"><a class="tool-tip-bottom" href="'+admin.SITE+'/news/'+response.data.url+'" title="'+news.lang.T_TIP_GOTO_NEWS+'" target="_blank">'+response.data.url+'</a></td>\
              <td>'+response.data.add_date+'\
                  <span class="future-public">'+response.data.add_date_future+'</span> \
                </td>   \
              <td class="actions text-right">\
                <ul class="action-list">\
                  <li class="edit-row tool-tip-bottom" id="'+response.data.id+'"><a href="#" class="fa fa-pencil" title="'+lang.EDIT+'"></a></li>\
                  <li class="delete-order tool-tip-bottom" id="'+response.data.id+'"><a href="#" class="fa fa-trash" title="'+lang.DELETE+'"></a></li>\
                </ul>\
              </td>\
           </tr>';


          // Вычисляем, по наличию характеристики 'id',
          // какая операция производится с продуктом, добавление или изменение.
          // Если id есть значит надо обновить запись в таблице.
          if(packedProperty.id){
            $('.news-tbody tr[id='+packedProperty.id+']').replaceWith(row);
          }else{
            // Если id небыло значит добавляем новую строку в начало таблицы.
             if($('.news-tbody tr:first').length>0){               
              $('.news-tbody tr:first').before(row);  
               if($('.noneNews').length==1){ 
                 $('.noneNews').remove();
               }
            } else{            
              $('.news-tbody').append(row);             
            }
           
          }

          // Закрываем окно
          admin.closeModal($('#add-plug-modal'));
          admin.initToolTip();
        }
      );
    },

    /**
     * Получает данные о новости с сервера и заполняет ими поля в окне.
     */
    editPage: function(id) {
      admin.ajaxRequest({
          pluginHandler: 'news', // имя папки в которой лежит данный плагин
          actionerClass: "News", // класс News в news.php - в папке плагина
          action: "getNews", // название действия в пользовательском  классе News
          id:id
      },
      news.fillFileds(),
      $('.add-product-form-wrapper')
      );
    },


    /**
     * Удаляет новость из БД сайта и таблицы в текущем разделе
     */
    deleteNews: function(id,imgFile) {
      if(confirm(lang.DELETE+'?')){
        admin.ajaxRequest({
          pluginHandler: 'news', // имя папки в которой лежит данный плагин
          actionerClass: "News", // класс News в news.php - в папке плагина
          action: "deleteNews", // название действия в пользовательском  классе News
          id: id
        },
        function(response) {
          admin.indication(response.status, response.msg);
           $('.product-table tr[id='+id+']').remove();

           if($('.product-table tr').length==1){
             var row = "<tr><td colspan="+$('.product-table th').length+">"+news.lang.PAGE_NONE+"</td></tr>"
             $('.news-tbody').append(row);             
           }
          }
        );
      }

    },


   /**
    * Заполняет поля модального окна данными
    */
    fillFileds:function() {
      return (function(response) {
        $('input[name=title]').val(response.data.title);
        $('input[name=url]').val(response.data.url);
        $('input[name=author]').val(response.data.author);
        $('textarea[name=html_content]').val(response.data.description);
        $('input[name=meta_title]').val(response.data.meta_title);
        $('input[name=meta_keywords]').val(response.data.meta_keywords);
        $('textarea[name=meta_desc]').val(response.data.meta_desc);
        $('input[name=add_date]').val(response.data.add_date);
        var src=admin.SITE+'/mg-admin/design/images/no-img.png';
        if(response.data.image_url){
          src=admin.SITE+'/uploads/news/'+response.data.image_url;
        }
        $('.prev-img').html('<img src="'+src+'" alt="" />');
        $('.symbol-count').text($('textarea[name=meta_desc]').val().length);
        $('.save-button').attr('id',response.data.id);
      })
    },
    
    /**
    * Запускаем генерацию метатегов по шаблонам из настроек
    */
    generateSeoFromTmpl: function() {
      if (!$('.seo-wrapper input[name=meta_title]').val()) {
        $('.seo-wrapper input[name=meta_title]').val($('#add-plug-modal input[name=title]').val());
      }
      if (!$('.seo-wrapper input[name=meta_keywords]').val()) {
        news.generateKeywords($('#add-plug-modal input[name=title]').val());
      }
      if (!$('.seo-wrapper textarea[name=meta_desc]').val()) {
        var short_desc = news.generateMetaDesc($('textarea[name=html_content]').val());
        $('#add-plug-modal .seo-wrapper textarea[name=meta_desc]').val($.trim(short_desc));
      }
      
      var data = {
        title: $('input[name=title]').val(),
        html_content: $('textarea[name=html_content]').val(),
        meta_title: $('input[name=meta_title]').val(),
        meta_keywords: $('input[name=meta_keywords]').val(),
        meta_desc: $('textarea[name=meta_desc]').val(),
      };

      admin.ajaxRequest({
        mguniqueurl:"action/generateSeoFromTmpl",
        type: 'page',
        data: data
      }, function(response) {
        $.each(response.data, function(key, value) {
          if (value) {
            if (key == 'meta_desc') {
              $('.seo-wrapper textarea[name='+key+']').val(value);
            } else {
              $('.seo-wrapper input[name='+key+']').val(value);
            }
          }
        });

        $('#add-plug-modal .js-meta-data').trigger('blur');
        admin.indication(response.status, response.msg);
      });
    },

    /**
    * Генерируем ключевые слова для категории
    * @param string title
    */
    generateKeywords: function(title) {
      // #MG_DEL (FREE)
      if (!$('#add-plug-modal .seo-wrapper input[name=meta_keywords]').val()) {	 
        var keywords = title;
        var keyarr = title.split(' ');
        
        if(keyarr.length == 1) {
          $('#add-plug-modal .seo-wrapper input[name=meta_keywords]').val(keywords);
          return;
        }
        
        for ( var i=0; i < keyarr.length; i++) {
          var word = keyarr[i].replace('"','');
          
          if (word.length > 3) {
            keywords += ', ' + word;
          } else {
            if(i!==keyarr.length-1){
                keywords += ', '+ word + ' ' + keyarr[i+1].replace(/"/g,'');
                i++; 
            }else{
                keywords += ', '+ word
            } 
          }         
        }
        
        $('#add-plug-modal .seo-wrapper input[name=meta_keywords]').val(keywords);
      }
      // #MG_DEL_END
    },

    /**
    * Генерируем мета описание
    */
    generateMetaDesc: function(description) {
      if (!description) {return '';}
      // #MG_DEL (FREE)
      var short_desc = description.replace(/<\/?[^>]+>/g, '');
      short_desc = admin.htmlspecialchars_decode(short_desc.replace(/\n/g, ' ').replace(/&nbsp;/g, '').replace(/\s\s*/g, ' ').replace(/"/g, ''));

      if (short_desc.length > 150) {
        var point = short_desc.indexOf('.', 150);
        short_desc = short_desc.substr(0, (point > 0 ? point : short_desc.indexOf(' ',150)));
      }

      return short_desc;
      // #MG_DEL_END
      /*#MG_ADD (FREE)
      return '';
      #MG_ADD_END*/
    },

   /**
    * Чистит все поля модального окна
    */
    clearFileds:function() {
      try{
        if(CKEDITOR.instances['news_content']) {
          CKEDITOR.instances['news_content'].destroy();
        }
      } catch(e) { }
      $('input[name=title]').val('');
      $('input[name=url]').val('');
      $('input[name=author]').val('');
      $('textarea[name=html_content]').val('');
      $('input[name=meta_title]').val('');
      $('input[name=meta_keywords]').val('');
      $('textarea[name=meta_desc]').val('');
      $('input[name=add_date]').val('');
      var src=admin.SITE+'/mg-admin/design/images/no-img.png';
      $('.prev-img').html('<img src="'+src+'" alt="" />');
      $('input[name=image_url]').val('');
      $('.symbol-count').text('0');
      $('.save-button').attr('id','');

      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display','none');
    },

   /**
    * Предпросмотр новости
    */
    previewPage: function() {
      admin.ajaxRequest({
          mguniqueurl: "action/getPreview", // действия для выполнения на сервере
          pluginHandler: 'news', // плагин для обработки запроса
          description: CKEDITOR.instances.news_content.getData(),
          title: $('input[name="title"]').val(),
          date_active_from: $('input[name="add_date"]').val(),
          image_url: $('.prev-img').find('img').attr('src'),
        },
        function(response){
          console.log(response);
          $('#previewContent').val(response.data);
          $('#previewer').submit();
        });

      // var content_page = $('textarea[name=html_content]').val();
      // $('#previewContent').val(content_page);
      // $('#previewer').submit();
    },

    /**
    * Добавляет изображение продукта
    */
    addImageToNews:function() {
      $('.img-loader').show();
      // отпраквка картинки на сервер

      $("#imageform").ajaxSubmit({
        type:"POST",
        url: "ajax",
        data: {
          pluginHandler:"news",
          actionerClass:"News"
          //action: "addImageNews"  передается в скрытом поле в силу специфики плагина form.js
        },
        cache: false,
        dataType: 'json',
        success: function(response){
          admin.indication(response.status, response.msg);
            if(response.status != 'error'){
              var src=admin.SITE+'/uploads/news/'+response.data.img;
              $('.prev-img').html('<img src="'+src+'" alt="" />');
            }else{
              $('.prev-img').html('');
            }
            $('.img-loader').hide();
        }
      });

    },


   /**
    * Удаляет изображение новости
    */
    delImageNews: function(id,imgFile) {
      if(confirm(lang.DELETE_IMAGE+'?')){
        admin.ajaxRequest({
          pluginHandler: 'news', // имя папки в которой лежит данный плагин
          actionerClass: "News", // класс News в news.php - в папке плагина
          action: "deleteImageNews", // название действия в пользовательском  классе News
          id:id,
          imgFile: imgFile
        },
        function(response) {
          admin.indication(response.status, response.msg);
          var src=admin.SITE+'/mg-admin/design/images/no-img.png';
          $('.prev-img').html('<img src="'+src+'" alt="" />');
          $('tr[id='+id+'] .uploads').attr('src',src);
          $('#photoimg').val('');
        }
        );
      }
    }

  }
})();

// инициализациямодуля при подключении
news.init();