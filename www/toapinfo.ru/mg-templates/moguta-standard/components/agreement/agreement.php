<?php
mgAddMeta('lib/dialog-polyfill/dialog-polyfill.css');
mgAddMeta('lib/dialog-polyfill/dialog-polyfill.js');
mgAddMeta('components/agreement/agreement.js');
if (class_exists('BackRing')) { ?>
<link rel="stylesheet" href="<?php echo PATH_SITE_TEMPLATE ?>/components/agreement/agreement.css">
<?php } else {
    mgAddMeta('components/agreement/agreement.css');
} ?>

<section class="agreement">
    <label class="agreement__label">
        <input class="agreement__checkbox js-agreement-checkbox-<?php echo $data['button']; ?>"
               type="checkbox">
        <span>
            <?php echo $data['text']; ?>
            <button type="button" class="agreement__btn agreement__btn_open js-open-agreement">
                <?php echo $data['textLink']; ?>
            </button>
        </span>
    </label>

    <dialog class="agreement__modal js-agreement-modal">
        <?php if (EDITION === 'saas') : ?>
            <div>
                <button class="agreement__btn agreement__btn_close js-close-agreement">
                    <svg class="icon icon--close">
                        <use xlink:href="#icon--close"></use>
                    </svg>
                </button>
                <?php
                echo MG::layoutManager('layout_agreement');
                ?>
            </div>
        <?php else: ?>
            <div>
                <button class="agreement__btn agreement__btn_close js-close-agreement">
                    <svg class="icon icon--close">
                        <use xlink:href="#icon--close"></use>
                    </svg>
                </button>
                <h2>Соглашение на обработку персональных данных</h2>
                <hr>
                <br>
            </div>
            Настоящим, я (далее – Лицо), даю свое согласие ООО «Интернет-магазин», юридический адрес: 115230, город
            Санкт-Петербург, Невский проспект, дом 30 (далее – Компания) на обработку своих персональных данных, указанных
            при
            оформлении заказа на сайте Компании для обработки моего заказа, коммуникации со мной в рамках обработки моего
            заказа, доставки заказанного мной товара, а также иных сопряженных с этим целей в рамках действующего
            законодательства РФ и технических возможностей Компании, а также для получения сервисного опроса по завершении
            оказания услуги или при невозможности оказания таковой.<br>
            <br>
            Обработка персональных данных Лица может осуществляться с помощью средств автоматизации и/или без использования
            средств автоматизации в соответствии с действующим законодательством РФ и положениями Компании. Настоящим Лицо
            соглашается на передачу своих персональных данных третьим лицам для их обработки в соответствии с целями,
            предусмотренными настоящим согласием, на основании договоров, заключенных Компанией с этими лицами, персональные
            данные Лица могут передаваться внутри группы лиц ООО «Интернет-магазин», включая трансграничную передачу.
            Настоящее
            согласие Лица на обработку его/ее персональных данных, указанных при оформлении заказа на сайте Компании,
            направляемых (заполненных) с использованием настоящего сайта, действует с момента оформления заказа на сайте
            Компании до момента его отзыва. Согласие на обработку персональных данных, указанных при оформлении заказа на
            сайте
            Компании, направляемых (заполненных) с использованием настоящего сайта, может быть отозвано Лицом при подаче
            письменного заявления (отзыва) в Компанию. Обработка персональных данных Лица прекращается в течение 2 месяцев с
            момента получения Компанией письменного заявления (отзыва) Лица и/или в случае достижения цели обработки и
            уничтожается в срок и на условиях, установленных законом, если не предусмотрено иное. Обезличенные персональные
            данные Лица могут использоваться Компанией в статистических (и иных исследовательских целей) после получения
            заявления (отзыва) согласия, а также после достижения целей, для которых настоящее согласие было получено.<br>
            <br>
            Настоящим Лицо подтверждает достоверность указанной информации.
        <?php endif; ?>
    </dialog>
</section>
