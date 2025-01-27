<div class="c-goods__item product-wrapper">

    <?php
    if (in_array(EDITION, array('market', 'gipermarket', 'saas')) && MG::getSetting('useFavorites') == 'true') {
        $favorites = explode(',', $_COOKIE['favorites']);
        if (in_array($data['item']['id'], $favorites)) {
            $_fav_style_add = 'display:none;';
            $_fav_style_remove = '';
        } else {
            $_fav_style_add = '';
            $_fav_style_remove = 'display:none;';
        }
        ?>
        <a role="button" href="javascript:void(0);"
           data-item-id="<?php echo $data['item']['id']; ?>"
           class="mg-remove-to-favorites"
           style="<?php echo $_fav_style_remove ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94">
                <path d="M26.285 2.486l5.407 10.956c.376.762 1.103 1.29 1.944 1.412l12.091 1.757c2.118.308 2.963 2.91 1.431 4.403l-8.749 8.528c-.608.593-.886 1.448-.742 2.285l2.065 12.042c.362 2.109-1.852 3.717-3.746 2.722l-10.814-5.685c-.752-.395-1.651-.395-2.403 0l-10.814 5.685c-1.894.996-4.108-.613-3.746-2.722l2.065-12.042c.144-.837-.134-1.692-.742-2.285L.783 21.014c-1.532-1.494-.687-4.096 1.431-4.403l12.091-1.757c.841-.122 1.568-.65 1.944-1.412l5.407-10.956c.946-1.919 3.682-1.919 4.629 0z"/>
            </svg>
            <span class="remove__text"><?php echo lang('inFav') ?></span>
            <span class="remove__hover"><?php echo lang('delFav') ?></span>
        </a>
        <a role="button"
           href="javascript:void(0);"
           data-item-id="<?php echo $data['item']['id']; ?>"
           class="mg-add-to-favorites"
           style="<?php echo $_fav_style_add ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94">
                <path d="M26.285 2.486l5.407 10.956c.376.762 1.103 1.29 1.944 1.412l12.091 1.757c2.118.308 2.963 2.91 1.431 4.403l-8.749 8.528c-.608.593-.886 1.448-.742 2.285l2.065 12.042c.362 2.109-1.852 3.717-3.746 2.722l-10.814-5.685c-.752-.395-1.651-.395-2.403 0l-10.814 5.685c-1.894.996-4.108-.613-3.746-2.722l2.065-12.042c.144-.837-.134-1.692-.742-2.285L.783 21.014c-1.532-1.494-.687-4.096 1.431-4.403l12.091-1.757c.841-.122 1.568-.65 1.944-1.412l5.407-10.956c.946-1.919 3.682-1.919 4.629 0z"/>
            </svg>
            <?php echo lang('toFav') ?>
        </a>
    <?php } ?>

    <div class="c-goods__left">
        <a class="c-goods__img" href="<?php echo $data['item']["link"] ?>">
            <div class="c-ribbon">
                <?php
                if (!isset($oldprice)) {
                  $oldprice = '';
                }
                if (!isset($price)) {
                  $price = '';
                }
                if (!empty($data['item']['old_price']) && $oldprice > $price) {
                    $price = floatval(MG::numberDeFormat($data['item']['price']));
                    $oldprice = floatval(MG::numberDeFormat($data['item']['old_price']));
                    $calculate = ($oldprice - $price) / ($oldprice / 100);
                    $result = "" . round($calculate) . " %";
                    echo '<div class="c-ribbon__sale"> -' . $result . ' </div>';
                }
                echo $data['item']['new'] ? '       <div class="c-ribbon__new">' . lang('stickerNew') . '</div>' : '';
                echo $data['item']['recommend'] ? ' <div class="c-ribbon__hit">' . lang('stickerHit') . '</div>' : '';
                ?>
            </div>
            <?php

            if (MODE_MINI_IMAGE != 'MODE_MINI_IMAGE' && MODE_MINI_IMAGE == "1") {
                echo mgImageProduct($data['item'], false, 'MIN');
            } else {
                echo mgImageProduct($data['item'], false, 'MID');
            }
            ?>
        </a>
        <?php if (class_exists('Rating')): ?>
            [rating id = "<?php echo $data['item']['id'] ?>"]
        <?php endif; ?>
    </div>
    <div class="c-goods__right">
        <div class="c-goods__price">
            <?php if ($data['item']["old_price"] != ""): ?>
                <s class="c-goods__price--old product-old-price old-price" <?php echo (!$data['item']['old_price']) ? 'style="display:none"' : '' ?>>
                    <?php echo MG::numberFormat($data['item']['old_price']); ?><?php echo $data['currency']; ?>
                </s>
            <?php endif; ?>
            <div class="c-goods__price--current product-price">
                <span><?php echo priceFormat($data['item']["price"]) ?></span>
                <span><?php echo $data['currency']; ?></span>
            </div>
        </div>
        <a class="c-goods__title"
           href="<?php echo $data['item']["link"] ?>">
            <span><?php echo $data['item']["title"] ?></span>
        </a>
        <div class="c-goods__description">
            <?php
            if ($data['item']["short_description"]) {
                echo MG::textMore($data['item']["short_description"], 80);
            } else {
                echo MG::textMore($data['item']["description"], 80);
            }
            ?>
        </div>
        <div class="c-goods__footer">
            <?php
            if (!isset($data['actionButton'])) {
                $data['actionButton'] = '';
            }
            if (isset($data['item']['buyButton'])) {
                if (class_exists('BuyClick') && strpos($data['item']['buyButton'], '[buy-click') === false) {
                    echo '[buy-click id="' . $data['item']['id'] . '"]';
                }
                echo $data['item']['buyButton'];
            } elseif (isset($data['item'][$data['actionButton']]) || isset($data['item']['actionCompare'])) {
                echo $data['item'][$data['actionButton']];
                echo $data['item']['actionCompare'];
                if (
                    class_exists('BuyClick') &&
                    strpos($data['item'][$data['actionButton']], '[buy-click') === false &&
                    strpos($data['item']['actionCompare'], '[buy-click') === false
                ) {
                    echo '[buy-click id="' . $data['item']['id'] . '"]';
                }
            } else { ?>
                <?php if (class_exists('BuyClick')): ?>
                    <!-- Плагин купить одним кликом-->
                    [buy-click id="<?php echo $data['item']['id'] ?>"]
                    <!--/ Плагин купить одним кликом-->
                <?php endif; ?>

              <?php if ($data['item']['count'] == 0) { ?>
                <a href="<?php echo SITE . '/' . ((MG::getSetting('shortLink') != 'true') && ($data['item']["category_url"] == '') ? 'catalog/' : $data['item']["category_url"]) . $data['item']["product_url"]; ?>"
                   class="js-product-more product-info action_buy_variant">
                  <?php echo lang('buttonMore'); ?>
                </a>
              <?php } else { ?>
                <a class="default-btn buy-product"
                   href="<?php echo SITE ?>/catalog?inCartProductId=<?php echo $data['item']['id']; ?>"
                   data-item-id="<?php echo $data['item']['id']; ?>">
                  <?php echo lang('relatedAddButton'); ?>
                </a>
              <?php }?>
            <?php } ?>
        </div>
    </div>
</div>