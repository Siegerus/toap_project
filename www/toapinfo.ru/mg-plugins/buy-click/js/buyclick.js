var buyClickFancyBoxReload = () => {
    if ($('.chd-request-price-btn:visible').length == 0) {
        $('.js-open-modal-bclick').show();
    }
    $('.js-open-modal-bclick').fancybox({
        animationEffect: "zoom-in-out",
        animationDuration: 366,
        autoFocus: true,
        touch: false,
        backFocus: true,
        trapFocus: true,
        hideScrollbar: true,
        afterShow: function () {
            $('body').addClass('stop-scrolling');
        },
        afterClose: function () {
            $('body').removeClass('stop-scrolling');
        }
    });
}

var initBuyClickPlugin = () => {
    buyOneClickModule.init();
    buyOneClickModule.targetElementsLastCount = $('.js-open-modal-bclick').length;
    buyClickFancyBoxReload();


    // Перезагрузка fancybox при динамическом добавлении целевого элемента
    // Например, при добавлении товаров на страницу через плагин "Показать ещё"
    // https://frontend-stuff.com/blog/debounce-in-javascript/
    var debounce = function(func, wait = 300, immediate = false) {
        var timeout;

        return function executedFunction() {
            var context = this;
            var args = arguments;

            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };

            var callNow = immediate && !timeout;

            clearTimeout(timeout);

            timeout = setTimeout(later, wait);

            if (callNow) {
                func.apply(context, args);
            }
        }
    };

    var onNewTargetButtonsAction = debounce(() => {
        buyClickFancyBoxReload();
    });
    if ('MutationObserver' in window) {
    //Для инициализации плагина на элементах подключаемых динамически
    var target = document.querySelector('body');

    // Конфигурация observer (за какими изменениями наблюдать)
    const config = {
        attributes: true,
        childList: true,
        subtree: true,
    };
    
    // Колбэк-функция при срабатывании мутации
    const callback = function (mutationsList, observer) {
        var targetElementsCurrentCount = $('.js-open-modal-bclick').length;
        if (targetElementsCurrentCount !== buyOneClickModule.targetElementsLastCount) {
            debounce
            buyOneClickModule.targetElementsLastCount = targetElementsCurrentCount;
            onNewTargetButtonsAction();
        }
    };

    // Создаём экземпляр наблюдателя с указанной функцией колбэка
    const observer = new MutationObserver(callback);
    // Начинаем наблюдение за настроенными изменениями целевого элемента
    observer.observe(target, config);
    } else {
    $(document.body).bind("DOMSubtreeModified", function () {
        var targetElementsCurrentCount = $('.js-open-modal-bclick').length;
        if (targetElementsCurrentCount !== buyOneClickModule.targetElementsLastCount) {
            buyOneClickModule.targetElementsLastCount = targetElementsCurrentCount;
            onNewTargetButtonsAction();
        }
    });
    }
}

var buyOneClickModule = (function () {
    return {
        targetElementsLastCount: 0,
        init: function () {
            if ($('.chd-request-price-btn').length == 0) {
                $('.js-open-modal-bclick').show();
            }
            var buyOneClick_recaptcha = '';
            if ($('.wrapper-modal-mg-buy-click').length == 0) {

                if (typeof agreement_buy_click == 'undefined') {
                    agreement_buy_click = '';
                }
                /*
                if ($('.agreement_buy_click_flag')) {
                    agreement_buy_click = '' + '<label class="agreement-container">' +
                        '<input class="agreement-data-checkbox-mg-send-order-click-button" type="checkbox"> ' +
                        '<span class="agreement-data-denied">Я даю согласие на обработку моих <a role="button" href="javascript:void(0);" class="show-more-agreement-data">персональных данных.</a>' +
                        '</span>' +
                        '</label>' +
                        '<br>';
                }*/
                //Постановка капчи с учтом локали
                
                
                var html = '' +
                    '<div role="document" id="modal-buy-click"  class="wrapper-modal-mg-buy-click" >' +
                        '<div class="wrapper-modal-mg-buy-click__inner" style="opacity:0">' +
                            '<div class="header-modal-mg-buy-click">' +
                                '<span class="title-modal-mg-buy-click"></span>' +
                            '</div>' +
                            '<div class="titles-modal-mg-buy-click">' +
                                '<span class="title js-bg-product-title"></span>' +
                                '<span class="variant"></span>' +
                            '</div>' +
                            '<div class="content-modal-mg-buy-click">' +
                                '<div class="mg-product-info" style="display:none">' +
                                    '<div class="mg-product-img">' +
                                        '<img class="product-image" src="" >' +
                                    '</div>' +
                                    '<div class="mg-price-buy-click">' +
                                        '<span class="nowrap"><span><span class="bk-label"></span><span class="bc-price"></span></span><span class="js-hide-if-single"><span class="bc-times bk-label">Количество:</span><span class="bc-count">1</span>шт.</span></span>' +
                                    '</div>' +    
                                '</div>' +
                                '<div class="mg-order-buy-click">' +
                                    '<form action="' + mgBaseDir + '/" method="post">' +
                                        '<ul class="modal-mg-order-list">' +
                                            '<li class="fio" style="display:none">' +
                                                '<label>Ваше имя:' +
                                                '<input type="text" name="bc-name" placeholder="Ваше Имя" value =""></label>' +
                                            '</li>' +
                                            '<li class="phone" style="display:none">' +
                                                '<label>Телефон:<span class="red-star">*</span>' +
                                                '<input type="text" name="phone" placeholder="Телефон" value ="" autocomplete="off"></label>' +
                                            '</li>' +
                                            '<li class="email" style="display:none">' +
                                                '<label>Ваш e-mail:<span class="red-star">*</span></span>' +
                                                '<input type="text" name="bc-email" placeholder="Ваш e-mail" value =""></label>' +
                                            '</li>' +
                                            '<li class="address" style="display:none">' +
                                                '<label for="bc-address-id">Адрес:><label>' +
                                                '<textarea id="bc-address-id" name="bc-address" placeholder="Адрес" value =""></textarea>' +
                                            '</li>' +
                                            '<li class="comment" style="display:none">' +
                                                '<label for="bc-comment-id">Комментарий:<label>' +
                                                '<textarea id="bc-comment-id" name="bc-comment" placeholder="Комментарий" value =  ""></textarea>' +
                                            '</li>';
                                            if ($('.mg-buy-click-button').data('captcha') === 'enabled') {
                                                html += '<li class="mg-cap" style="display:none">' +
                                                    '<div class="cap-left">' +
                                                        '<img style="margin-top: 5px; border: 1px solid gray;" src = "' + '" width="140" height="36">' +
                                                        '<span>Введите текст с картинки:<span class="red-star">*</span> </span>' +
                                                        '<input type="text" name="capcha" class="captcha">' +
                                                    '</div>' +
                                                    '<div style="clear:both;"></div>' +
                                                '</li>';
                                            }
                                        html += '</ul>' +
                                    '</form>' +
                                '</div>' +
                            '</div>' +
                            '<div class="mg-action-buttons">' +
                                '<div class="buyClick_agreement">' + agreement_buy_click + '</div>' +
                                '<button type="submit" class="mg-send-order-click-button mg-buy-btn"><span>Оставить заявку</span></button>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                    if ($('.mg-buy-click-button').length > 0){
                        $('body').append(html);
                    }
                
            }

            // Открытие пользовательского соглашения
            $('body').on('click', '.agreement__btn_open', function () {$('body .agreement__modal').show(); });
            //Закрытие пользовательского соглашения
            $('body').on('click', '.agreement__btn_close', function () {$('body .agreement__modal').hide(); });

            // если выбран вариант, которого нет на складе
            $('.block-variants input[type=radio]:checked').each(function () {
                if ($(this).data('count') == 0) {
                    if ($('.wrapper-mg-buy-click').length > 1) {
                        $(this).parents('.product-wrapper').find('.wrapper-mg-buy-click .mg-buy-click-button').hide();
                    } else {
                        $('.wrapper-mg-buy-click .mg-buy-click-button').hide();
                    }
                }
            });

            // при нажатии на кнопку купить открывается модальное окно
            $('body').on('click', '.mg-buy-click-button', function () {

                var id = $(this).data('product-id');
                var var_id = '';
                if ($(this).parents('.product-wrapper').length) {
                    var_id = $(this).parents('.product-wrapper').find('.block-variants input[type=radio]:checked').attr('value');
                } else {
                    var_id = $('.block-variants input[type=radio]:checked').attr('value');
                }

                openOrderForm(id, var_id);
                const captchaEnabled = $(this).data('captcha') === 'enabled';
                $('.buyClick_agreement').show();
                $('.wrapper-modal-mg-buy-click .mg-action-buttons .mg-send-order-click-button').prop('disabled', false);
                if (captchaEnabled) {
                    var capchaSrc = mgBaseDir + ((langP=='LANG')?'':('/'+langP))+"/captcha.html?t=" + Date.now();
                    if ($('.wrapper-modal-mg-buy-click .cap-left img').attr('src') == '') {
                        $('.wrapper-modal-mg-buy-click .cap-left img').attr('src', capchaSrc);
                    }
                }
                if ($('.wrapper-modal-mg-buy-click .loading-send-order').data('buy')== id) {
                  return true;
                }
                else {
                  $('.wrapper-modal-mg-buy-click .loading-send-order').data('buy','');
                  $('.wrapper-modal-mg-buy-click .loading-send-order').hide();
                  $('.mg-action-buttons .mg-send-order-click-button').show();
                  $('.mg-price-buy-click').show();
                  $('.content-modal-mg-buy-click').show();
                }

                
                var count = 1;
                var price = '';

                // данные из мини-карточки товара (из каталога) или из полной карточки товара
                if ($(this).parents('.product-wrapper').length) {
                    price = $(this).parents('.product-wrapper').find(".product-price .product-default-price:first").text();
                    count = $(this).parents('.product-wrapper').find(".property-form input[name=amount_input]").val();
                    if (price == '') {
                        price = $(this).parents('.product-wrapper').find(".product-price").text();
                    }
                } else if ($(this).parents('.js-product-page').length) {
                    price = $(this).parents('.js-product-page').find(".product-price .product-default-price:first").text();
                    count = $(this).parents('.js-product-page').find(".property-form input[name=amount_input]").val();
                } else {
                    price = $('body').find(".product-status-list li .price:first").text();
                    count = $('body').find(".buy-block-inner .property-form .cart_form input[name=amount_input]").val();
                }

                if (!price) {
                    price = $('[data-buy-click-price]').text();
                }

                if(!count) {
                    count = $('[data-buy-click-count] input[name=amount_input]').val();
                }

                if (!price) {
                    price = $('[data-buy-click-price]').text();
                }

                var countWrapper = $('body').find('.js-hide-if-single');
                countWrapper.show()

                if (price) {
                    $('.wrapper-modal-mg-buy-click  .mg-price-buy-click .bc-price').text(price);
                }
                $('.wrapper-modal-mg-buy-click  .mg-price-buy-click .bc-count').text(count);

                var image = $('img[data-product-id=' + id + ']').attr('src');
                if (image) {
                    $('.wrapper-modal-mg-buy-click  .mg-product-img img').attr('src', image);
                }

                $('.wrapper-modal-mg-buy-click .error').remove();

                // $('.mg-order-buy-click input[name=bc-phone]').mask(phoneMask.replace(/#/g, '9'));
            });

            // оформление заказа по нажатию кнопки купить
            //$('.mg-send-order-click-button').click(function () {
                
            $('body').on('click', '.mg-send-order-click-button', function () {
                var checkbox = $('.js-agreement-checkbox-mg-send-order-click-button');
                if(checkbox.length != 0){
                    var errorClass = 'agreement__label_error';
                    if(checkbox.is(':checked')) {
                        checkbox.parent().removeClass(errorClass);
                    }
                    else{
                        checkbox.parent().addClass(errorClass);
                        return false;
                    }
                }
                var button = $(this);
                button.prop('disabled', true);
                var id = $(this).attr('data-id');
                var name = $(this).parents('.wrapper-modal-mg-buy-click').find('input[name=bc-name]');
                var phone = $(this).parents('.wrapper-modal-mg-buy-click').find('.mg-order-buy-click input[name=phone]');
                var email = $(this).parents('.wrapper-modal-mg-buy-click').find('input[name=bc-email]');
                var address = $(this).parents('.wrapper-modal-mg-buy-click').find('textarea[name=bc-address]');
                var comment = $(this).parents('.wrapper-modal-mg-buy-click').find('textarea[name=bc-comment]');
                var capcha = $(this).parents('.wrapper-modal-mg-buy-click').find('input[name=capcha]').val();

                if (!capcha) {
                    if ($('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap .g-recaptcha-template').length) {
                        capcha = grecaptcha.getResponse(buyOneClick_recaptcha);
                    }

                    if ($('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap .recaptcha-holder-template').length) {
                        if (!buyOneClickModule.recaptchaToken) {
                            grecaptcha.execute(buyOneClick_recaptcha);
                            return false;
                        } else {
                            capcha = buyOneClickModule.recaptchaToken;
                        }
                    }
                }
                buyOneClickModule.recaptchaToken = null;

                $.ajax({
                    type: "POST",
                    url: mgBaseDir + "/ajaxrequest",
                    dataType: 'json',
                    data: {
                        mguniqueurl: "action/sendOrderBuyClick", // действия для выполнения на сервере
                        pluginHandler: 'buy-click',
                        name: name.val(),
                        phone: phone.val(),
                        email: email.val(),
                        address: address.val(),
                        comment: comment.val(),
                        capcha: capcha,
                    },
                    success: function (response) {
                        button.prop('disabled', false);
                        if (response.status != 'error') {
                            $('.mg-action-buttons .mg-send-order-click-button').hide();
                            $('.agreement-container').hide();
                            $('.buyClick_agreement').hide();
                            $('.wrapper-modal-mg-buy-click .error').remove();
                            $('.loading-send-order').remove();
                            likeAddToCart(id);
                        } else {
                            $('.wrapper-modal-mg-buy-click .error').remove();
                            $('.title-modal-mg-buy-click').after(response.data.msg);
                            $('.loading-send-order').remove();
                        }
                    }
                });
            });
            
            /**
             * функция добавления товара в корзину
             * @param {type} e
             * @returns {undefined}
             */

            function likeAddToCart(id) {
                id = parseInt(id);
                if ($('.buy-block').find(".property-form").length) {
                    var request = $('.buy-block').find(".property-form").formSerialize();
					if (request.indexOf("inCartProductId") == -1) {
					  request = request +'&inCartProductId=' + id;
					}
                } else {
                    var inCartProd = 'inCartProductId=' + id;
                    var request = inCartProd + '&amount_input='+$('#modal-buy-click .mg-price-buy-click .bc-count').html();
                    var variant = $('.mg-buy-click-button[data-product-id=' + id + ']').parents('.product-wrapper').find('.block-variants input[type=radio]:checked').val();
                    if (variant) {
                        request = request + '&variant=' + variant;
                    }
                }
                $.ajax({
                    type: "POST",
                    url: mgBaseDir + "/cart?plugin=buy-click",
                    data: "ajax=buyclickflag&updateCart=1&" + request,
                    dataType: "json",
                    cache: false,
                    success: function (response) {
                        if ('success' == response.status) {
                            $('.mg-action-buttons').before("<span data-buy=" + id + " class='loading-send-order'>Спасибо за заявку! Наши менеджеры свяжутся с Вами!</span>");
                            $('.mg-action-buttons .mg-send-order-click-button').hide();
                            $('.mg-price-buy-click').hide();
                            $('.content-modal-mg-buy-click').hide();
                            $('.agreement-container').hide();
                            $('.titles-modal-mg-buy-click').hide();
                        } else {
                            $('.mg-action-buttons').before("<span class='loading-send-order error'>Ошибка при отправке заявки. "+response.msg+"</span>");
                        }
                    }
                });
            }

            $('body').on('change', '.block-variants input[type=radio]', function () {
                if ($(this).data('count') == 0) {
                    if ($('.wrapper-mg-buy-click').length > 1) {
                        $(this).parents('.product-wrapper').find('.wrapper-mg-buy-click .mg-buy-click-button').hide();
                    } else {
                        $(this).parents('.product-wrapper').find('.wrapper-mg-buy-click .mg-buy-click-button').hide();
                    }
                } else {
                    if ($('.wrapper-mg-buy-click').length > 1) {
                        $(this).parents('.product-wrapper').find('.wrapper-mg-buy-click .mg-buy-click-button').show();
                    } else {
                        $(this).parents('.product-wrapper').find('.wrapper-mg-buy-click .mg-buy-click-button').show();
                    }
                }
            });

            /**
             * функция загрузки формы для заказа
             * @param {type} e
             * @returns {undefined}
             */
            function openOrderForm(id, var_id) {
                $.ajax({
                    type: "POST",
                    url: mgBaseDir + "/ajaxrequest",
                    dataType: 'json',
                    data: {
                        mguniqueurl: "action/buildOrderForm", // действия для выполнения на сервере
                        pluginHandler: 'buy-click',
                        id: id,
                        var_id: var_id,
                    },
                    success: function (response) {
                        $('.wrapper-modal-mg-buy-click .mg-action-buttons .mg-send-order-click-button').attr('data-id', id);
                        if (response.data.options.header != '') {
                            $('.wrapper-modal-mg-buy-click .title-modal-mg-buy-click').html(response.data.options.header);
                        }
                        $('.js-bg-product-title').html(response.data.product_title);
                        $('.wrapper-modal-mg-buy-click  .variant').html(response.data.variant_title);
                        if (response.data.options.product == 'true') {
                            $('.wrapper-modal-mg-buy-click .mg-product-info').css("display", "block");
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list .variant').hide();
                            const img = mgBaseDir + '/uploads/'  + response.data.product_image;
                            if (
                                !$('.wrapper-modal-mg-buy-click  .mg-product-img img').attr('src') ||
                                !$('.wrapper-modal-mg-buy-click  .mg-product-img img').attr('src') != img
                            ) {
                                $('.wrapper-modal-mg-buy-click  .mg-product-img img').attr('src', img);
                            }
                        }

                        if (response.data.options.name == 'true') {
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.fio').css("display", "block");
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list input[name=bc-name]').val(response.data.user.name);
                        }
                        if (response.data.options.phone == 'true') {
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.phone').css("display", "block");
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list input[name=phone]').val(response.data.user.phone);
                        }
                        if (response.data.options.email == 'true') {
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.email').css("display", "block");
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list input[name=bc-email]').val(response.data.user.email);
                        }
                        if (response.data.options.address == 'true') {
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.address').css("display", "block");
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list textarea[name=bc-address]').val(response.data.user.address);
                        }
                        if (response.data.options.comment == 'true') {
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.comment').css("display", "block");
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list textarea[name=bc-comment]').val('');
                        }
                        if (response.data.options.capcha == 'true') {
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap').css("display", "block");
                            $('.wrapper-modal-mg-buy-click .modal-mg-order-list input[name=capcha]').val('');
                            if (response.data.options.recaptcha == 'true') {
                                $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap').html(response.data.recaptchahtml);

                                if ($('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap .g-recaptcha-template').length && !$('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap .g-recaptcha-template iframe').length) {
                                    $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap').find('.g-recaptcha-template').attr('id', 'buyClick_recaptcha');
                                    var skey = $('#buyClick_recaptcha').data('sitekey');
                                    buyOneClick_recaptcha = grecaptcha.render('buyClick_recaptcha', {
                                        sitekey: skey
                                    });
                                    $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap').find('.g-recaptcha-template').removeAttr('id');
                                }

                                if ($('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap .recaptcha-holder-template').length && !$('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap .recaptcha-holder-template iframe').length) {
                                    $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap').find('.recaptcha-holder-template').attr('id', 'buyClick_recaptcha');
                                    var skey = $('#buyClick_recaptcha').data('sitekey');
                                    buyOneClick_recaptcha = grecaptcha.render('buyClick_recaptcha', {
                                        sitekey: skey,
                                        callback: function (recaptchaToken) {
                                            buyOneClickModule.recaptchaToken = recaptchaToken;
                                            $('.mg-send-order-click-button:visible').click();
                                        }
                                    });
                                    $('.wrapper-modal-mg-buy-click .modal-mg-order-list li.mg-cap .recaptcha-holder-template').removeAttr('id');
                                }

                            }
                        }      
                        $('.wrapper-modal-mg-buy-click__inner').css('opacity', '1');

                    }
                });

                return true;

            }

        }
    }
})();
$(document).ready(function () {
    initBuyClickPlugin();
});
