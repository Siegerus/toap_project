// Polyfill for css vars
cssVars();

// Функция для переключения меню
function burgMenuOnOff() {
    const headerMenuBG = document.querySelector(".header__cont-nav");
    const headerMenu = document.querySelector(".header__cont-nav ul");
    headerMenuBG.classList.toggle("active");
    headerMenu.classList.toggle("active");
}




$(document).ready(function () {
    // add active link
    // ------------------------------------------------------------
$('.header__cont-nav a').each(function () {
    var location = window.location.href; // Получаем текущий URL страницы
    var link = this.href; // Получаем href текущей ссылки

    // Проверяем, совпадает ли URL или если URL пустой (главная страница)
    if (location == link || location === "http://ваш_домен/") { // Замените "ваш_домен" на актуальный
        // Назначаем класс 'active' родительскому <li> внутри .header__cont-nav
        $(this).closest('li').addClass('active'); // Назначаем класс active ближайшему <li>
    }
});

    var $page = $('html, body');
$('a[href*="#"]').click(function() {
    $page.animate({
        scrollTop: $($.attr(this, 'href')).offset().top
    }, 400);
    return false;
});


    // agreement
    // ------------------------------------------------------------
    $('.l-body').on('change', '[type="checkbox"]', function () {
        if ($(this).prop('checked')) {
            $(this).closest('label').removeClass('nonactive').addClass('active');
        }
        else {
            $(this).closest('label').removeClass('active').addClass('nonactive');
        }
    });

    // op-field-check
    // ------------------------------------------------------------
    $('.l-body').on('change', '.op-field-check [type="radio"]', function () {
        $('.op-field-check [name='+$(this).attr('name')+']').closest('label').removeClass('active').addClass('nonactive');
        if ($(this).prop('checked')) {
           $(this).closest('label').removeClass('nonactive').addClass('active');
        }
        else{
            $(this).closest('label').removeClass('active').addClass('nonactive');
        }
    });

    // order
    // ------------------------------------------------------------
    $('.c-order__checkbox label').on('click', function () {
        if ($(this).children('[type="checkbox"]').is(':checked')) {
            $(this).removeClass('nonactive').addClass('active');
        } else {
            $(this).removeClass('active').addClass('nonactive');
        }
    });
    $('.c-order__radiobutton label, .order-storage label').on('click', function () {
        if ($(this).children('[type="radio"]').is(':checked')) {
            $(this).removeClass('nonactive').addClass('active');
            $(this).siblings('label').removeClass('active');
        }
    });

    //эмуляция радиокнопок в форме характеристик продукта (страница товара, миникарточка, корзина, страница заказа)
    var form = $('.js-product-form');
    $(form).on('change', '[type=radio]', function () {
        $(this).parents('p').find('input[type=radio]').prop('checked', false);
        $(this).prop('checked', true);
        $(this).parents('p').find('label').removeClass('active');
        if ($(this).parents('p').length) {
            $(this).parent().addClass('active');
        }
    });

    //эмуляция чекбоксов в форме характеристик продукта (страница товара, миникарточка, корзина, страница заказа)
    $(form).on('change', '[type=checkbox]', function () {
        $(this).parent().toggleClass('active');
    });


    $('.spoiler-title').on('click', function () {
        $(this).parents('.spoiler').toggleClass('_active');
    });
}); // end ready

$('input, textarea').each(function () {
    var $elem = $(this);
    if ($elem.attr('placeholder') && !$elem[0].placeholder) {
        var $label = $('<label class="placeholder"></label>').text($elem.attr('placeholder'));
        $elem.before($label);
        $elem.blur();
        if ($elem.val() === '') {
            $label.addClass('visible');
        }
        $label.click(function () {
            $label.removeClass('visible');
            $elem.focus();
        });
        $elem.focus(function () {
            if ($elem.val() === '') {
                $label.removeClass('visible');
            }
        });
        $elem.blur(function () {
            if ($elem.val() === '') {
                $label.addClass('visible');
            }
        });
    }
});


function toTest(text) {
    if(!document.querySelector(".test-section")) return;

    let button = document.querySelector(".card-item__button");
    let block = document.querySelector(".test-section__item");
    let interval;
    let timeout;
    button.addEventListener("click", (e) => {
        let target = e.target.closest(".card-item__button");
        if(!target) return;

        alert(text);

        block.style.position = "relative";
        block.style.left = 100 + "%";
        block.style.transform = "translate(-50%)";

        function returnPosition(item) {
            item.style.position = "";
            block.style.left = "";
            block.style.transform = "translate(0)";
        }

        let delay = setTimeout(()=> returnPosition(block), 1000);

        function toBlink() {
            if(interval) clearInterval(interval);
            if(timeout) clearInterval(timeout);

            interval = setInterval(() => {
                if(block.style.backgroundColor == "grey") block.style.backgroundColor = "white";
                else block.style.backgroundColor = "grey";
            }, 400);

            timeout = setTimeout(() => {
                clearInterval(interval);
                block.style.backgroundColor = "grey";
            }, 5000);
        }
        toBlink();
    });
}
window.addEventListener("DOMContentLoaded", () => toTest("Hellow! Meow..."));

