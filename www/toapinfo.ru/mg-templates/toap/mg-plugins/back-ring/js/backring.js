var initBackRing = () => {
  $.datepicker.regional['ru'] = {
    closeText: 'Закрыть',
    prevText: '<Пред',
    nextText: 'След>',
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

  var lang = []
  

  if (typeof availableTags !== 'undefined' && availableTags) {
    $(".content-modal-back-ring input[name=city_id]").autocomplete({
      source: availableTags
    });
  }
  var backring_recaptcha = '';

  $(".ui-autocomplete").css('z-index', '1000');
  
  $('.content-modal-back-ring input[name=date_callback]').datepicker({dateFormat: "yy-mm-dd"});
  if(typeof $.mask != 'undefined') {
    //Получаем префикс телефона из атрибута
    let phoneInput = $(".content-modal-back-ring input[name=phone]");
    phoneInput.mask(phoneInput.data("mask")+" (999) 999-99-99");
  }

  $('.back-ring-button').click(function() {
    if ($('.wrapper-modal-back-ring').is(':visible')) {
      return false;
    }
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/ajaxrequest",
      data: {
        pluginHandler: "back-ring",
        actionerClass: "Pactioner",
        action: "getLang"
      },
      dataType: "json",
      cache: false,
      success: function(response) {
        lang = response.data.lang;
        $.datepicker.setDefaults($.datepicker.regional[response.data.regional]);
      }
    });
    window.backRingRecaptchaToken = null;
    //$("html, body").animate({ scrollTop: 0 }, "fast");

    if ($('.wrapper-modal-back-ring').first().find('.g-recaptcha-template').length && !$('.wrapper-modal-back-ring').first().find('.g-recaptcha-template').find('iframe').length) {
      $('.wrapper-modal-back-ring').first().find('.g-recaptcha-template').attr('id', 'backring_recaptcha');
      var skey = $('#backring_recaptcha').data('sitekey');
      backring_recaptcha = grecaptcha.render('backring_recaptcha', {
          sitekey: skey
      }, true);
      $('.wrapper-modal-back-ring').first().find('.g-recaptcha-template').removeAttr('id');
    }

    if ($('.wrapper-modal-back-ring').first().find('.recaptcha-holder-template').length && !$('.wrapper-modal-back-ring').first().find('.recaptcha-holder-template').find('iframe').length) {
      $('.wrapper-modal-back-ring').first().find('.recaptcha-holder-template').attr('id', 'backring_recaptcha');
      var skey = $('#backring_recaptcha').data('sitekey');
      backring_recaptcha = grecaptcha.render('backring_recaptcha', {
          sitekey: skey,
          callback: function (recaptchaToken) {window.backRingRecaptchaToken = recaptchaToken;$('.send-ring-button:visible').click();}
      }, true);
      $('.wrapper-modal-back-ring').first().find('.recaptcha-holder-template').removeAttr('id');
    }
      

    openModal($('.wrapper-modal-back-ring').first());
    var off = $(document).scrollTop() + 50;
    $('.wrapper-modal-back-ring').first().offset({ top: off });
    $('.content-modal-back-ring input[name=name]').val('');
    $('.content-modal-back-ring textarea[name=comment]').val('');
    $('.content-modal-back-ring input[name=phone]').val('');
    $('.content-modal-back-ring input[name=city_id]').val('');
    $('.content-modal-back-ring select[name=mission]').val('');
    $('.content-modal-back-ring input[name=date_callback]').val('');

    $('.wrapper-modal-back-ring .error').remove();
  });

  $('.close-ring-button').click(function() {
    $('.wrapper-modal-back-ring').hide();
    closeModal($('.wrapper-modal-back-ring'));
  });
  

  // Receiving and transferring user agreement on AJAX
  $('.enter-text').click(function()  {
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/ajaxrequest",
      data: {
        pluginHandler: "back-ring",
        actionerClass: "Pactioner",
        action: "getAgreement"
      },
      dataType: "json",
      cache: false,
      success: function(message) {
        $('.agr-text').html(message.data['agr']);
        $('.agreement__modal_ring').show();
      } 
    });
  });

  $('.agreement__btn_close_ring').click(function() {
    $('.agreement__modal_ring').hide();
  });
  
  $('body').on('click', '.send-ring-button', function() {
  

    var name = $(this).parents('.content-modal-back-ring').find('input[name=name]');
    var comment = $(this).parents('.content-modal-back-ring').find('textarea[name=comment]');
    var phone = $(this).parents('.content-modal-back-ring').find('input[name=phone]');
    var city_id = $(this).parents('.content-modal-back-ring').find('input[name=city_id]');
    var mission = $(this).parents('.content-modal-back-ring').find('select[name=mission]');
    var from = $(this).parents('.content-modal-back-ring').find('select[name=from]');
    var to = $(this).parents('.content-modal-back-ring').find('select[name=to]');
    var date_callback = $(this).parents('.content-modal-back-ring').find('input[name=date_callback]');
    var captcha = $(this).parents('.content-modal-back-ring').find('input[name=capcha]').val();
    var agreement = $(this).parents('.content-modal-back-ring').find('input[name=agreement]').val();

    if (!captcha) {
      if ($('.wrapper-modal-back-ring').first().find('.g-recaptcha-template').length){
        captcha = grecaptcha.getResponse(backring_recaptcha);
      }
    }

    if (!captcha) {
      if ($('.wrapper-modal-back-ring').first().find('.recaptcha-holder-template').length){
        if (!window.backRingRecaptchaToken) {
          grecaptcha.execute(backring_recaptcha);
          return false;
        } else {
          captcha = window.backRingRecaptchaToken;
        }
      }
    }

    var time_callback = 'с ' + from.val() + ' до ' + to.val();
    if (from.parents('li').css('display') == 'none') {
      time_callback = '';
    }

    if($('.back-ring-agreement__input').prop( "checked" ) == false){
      $('.wrapper-modal-back-ring .error').remove();
      $('.title-modal-back-ring').after('<div class="error">'+lang['VALID_ALL_FIELD']+'</div>');
      return false;
    }
    if (phone.val() == "") {
      $('.wrapper-modal-back-ring .error').remove();
      $('.title-modal-back-ring').after('<div class="error">'+lang['VALID_ALL_FIELD']+'</div>');
      return false;
    }

    $('.send-ring-button').hide();
    $('.send-ring-button').before("<span class='loading-send-ring'>"+lang['WAIT']+"</span>");

    $.ajax({
      type: "POST",
      url: mgBaseDir + "/ajaxrequest",
      dataType: 'json',
      data: {
        mguniqueurl: "action/sendOrderRing", // действия для выполнения на сервере
        pluginHandler: 'back-ring',
        name: name.val(),
        comment: comment.val(),
        phone: phone.val(),
        city_id: city_id.val(),
        mission: mission.val(),
        date_callback: date_callback.val(),
        time_callback: time_callback,
        invisible: 1,
        status_id: 1,
        pub: 1,
        capcha: captcha,
        agreement: agreement        
      },
      success: function(response) {
        if (response.status != 'error') {
          $('.content-modal-back-ring').html(lang['SENDED1'] + response.data.row.id + lang['SENDED2']);        
          $('.send-ring-button').show();
          $('.wrapper-modal-back-ring .error').remove();
          $('.loading-send-ring').remove();
          //closeModal($('.wrapper-modal-back-ring'));
        } else {
          $('.wrapper-modal-back-ring .error').remove();
          $('.title-modal-back-ring').after(response.data.msg);
          $('.send-ring-button').show();
          $('.loading-send-ring').remove();
        }
      }
    });
  });

  /**
   * Открывает модальное окно
   */
  function openModal(object) {
    overlay();
    object.fadeIn(300);
    object.css('z-index', 1000);
  }

  function captcha(object) {
    overlay();
    object.fadeIn(300);
    object.css('z-index', 1000);
  }

  /**
   * Закрывает модальное окно
   */
  function closeModal(object) {
    object.fadeOut(300);
    $("#overlay").remove();
  }

  /**
   * Фон для заднего плана при открытии всплывающего окна
   */
  function overlay() {
    var docHeight = $(document).height();
    $(".wrapper-modal-back-ring").first().before("<div id='overlay'></div>");
    $("#overlay").height(docHeight);
    $("#overlay").css("z-index", "999");

  }
}
$(document).ready(function() {
  initBackRing();
});
