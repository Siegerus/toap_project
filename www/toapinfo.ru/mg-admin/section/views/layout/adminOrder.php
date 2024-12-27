<?php
MG::set('controller', 'controllers_product'); // Да простят меня боги за этот костыль (с) Санёк
MG::setSizeMapToData($data);
MG::set('controller', 'controllers_ajax');
?>

<?php if (!empty($data['blockVariants'])) { ?>
    <div class="c-variant block-variants">
        <div class="c-variant__title">
            <?php if ($data['sizeMap'] == '') {
                echo lang('variantTitle');
            } ?>
        </div>
        <div class="c-variant__scroll">
            <?php if ($data['sizeMap'] != '') {
                echo '<div class="sizeMap-row">';
                $color = '';
                $countColor = 0;
                foreach ($data['sizeMap'] as $item) {
                    MG::loadLocaleData($item['id'], LANG, 'property_data', $item);
                    if ($item['type'] == 'color') {
                        $countColor++;
                        if ($item['img']) {
                            $color .= '<div class="color" data-id="' . $item['id'] . '" style="background:url(' . SITE . '/' . $item['img'] . ');background-size:cover;" title="' . $item['name'] . '"></div>';
                        } else {
                            $color .= '<div class="color" data-id="' . $item['id'] . '" style="background-color:' . $item['color'] . ';" title="' . $item['name'] . '"></div>';
                        }
                        $colorName = $item['pName'];
                    }
                }

                if (($color != '')) {
                    $colorTmp = explode('[prop attr=', $colorName);
                    if (isset($colorTmp[1])) {
                        $colorTmp2 = explode(']', $colorTmp[1]);
                        if ($colorTmp2[0]) {
                            $colorTmp2 = ' (' . $colorTmp2[0] . ')';
                        } else {
                            unset($colorTmp2);
                        }
                        $colorName = $colorTmp[0] . $colorTmp2;
                    }
                    $colorFull = '<div class="color-block"><span class="color-block__title">' . $colorName . ':</span>' . $color . '</div>';
                } else {
                    $colorFull = '';
                }

                $size = '';
                foreach ($data['sizeMap'] as $item) {
                    MG::loadLocaleData($item['id'], LANG, 'property_data', $item);
                    if ($item['type'] == 'size') {
                        $size .= '<div class="size" data-id="' . $item['id'] . '"><span>' . $item['name'] . '</span></div>';
                        $sizeName = $item['pName'];
                    }
                }
                if ($size != '') {
                    $sizeTmp = explode('[prop attr=', $sizeName);
                    if (isset($sizeTmp[1])) {
                        $sizeTmp2 = explode(']', $sizeTmp[1]);
                        if ($sizeTmp2[0]) {
                            $sizeTmp2 = ' (' . $sizeTmp2[0] . ')';
                        } else {
                            unset($sizeTmp2);
                        }
                        $sizeName = $sizeTmp[0] . $sizeTmp2;
                    }
                    $sizeFull = '<div class="size-block"><span class="size-block__title">' . $sizeName . ':</span>' . $size . '</div>';
                } else {
                    $sizeFull = '';
                }

                echo $sizeFull;
                echo $colorFull;

                echo '</div>';
            }
            $i = 0;
            ?>
        </div>
    </div>
<?php } ?>


<div class="c-form">
    <ul class="accordion" data-accordion="" data-multi-expand="true" data-allow-all-closed="true">
        <?php if (!empty($data['blockVariants'])) {
            $j = 0 ?>
            <li class="accordion-item" data-accordion-item="" <?php if ($data['sizeMap'] != '') echo "style='display:none;'" ?>><a class="accordion-title"
                                                                 href="javascript:void(0);"><?php echo $lang['options']; ?></a>
                <div class="accordion-content" data-tab-content="">
                    <div class="c-variant block-variants">
                        <div class="c-variant__scroll">
                            <table class="variants-table">
                                <?php
                                $i = 0;
                                foreach ($data['blockVariants'] as $variant) {

                                    ?>
                                    <tr class="js-check-variant c-variant__row variant-tr <?php echo !$j++ ? 'active-var' : '' ?>"
                                        data-count="<?php echo $count; ?>" data-color="<?php echo $variant['color'] ?>"
                                        data-size="<?php echo $variant['size'] ?>">
                                        <td class="c-variant__column c-variant__column_radio">
                                            <input type="radio" id="variant-<?php echo $variant['id']; ?>"
                                                   class="js-variant-radio-btn"
                                                   aria-label="Выбрать вариант"
                                                   data-count="<?php echo $count; ?>" name="variant"
                                                   value="<?php echo $variant['id']; ?>"
                                                <?php echo !$i++ ? 'checked=checked' : '' ?>>
                                        </td>
                                        <td>
                                            <?php $src = mgImageProductPath($variant['image'], $variant['product_id'], 'small');
                                            echo !empty($variant['image']) ? '
                                              <span class="c-variant__img"><img src="' . $src . '" alt="image"></span>
                                          ' : '' ?>
                                        </td>
                                        <td>
                                          <span class="c-variant__name">
                                              <?php echo $variant['title_variant'] ?>
                                          </span>
                                        </td>
                                        <td>
                                          <span class="c-variant__price <?php if ($variant['activity'] === "0" || $variant['count'] == 0) {
                                              echo 'c-variant__price--none';
                                          } ?>">
                                              <span class="c-variant__price--current">
                                                  <?php echo $variant['price'] ?><?php echo MG::getSetting('currency') ?>
                                              </span>
                                          </span>
                                        </td>
                                        <td>
                                          <span class="c-variant__count">
                                              Кол-во: <?php echo ($variant['count'] != '-1') ? $variant['count'] : '∞' ?>
                                          </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>
<div class="buy-container">
    <div class="c-buy hidder-element">
        <input type="hidden" name="inCartProductId" value="<?php echo $data['id'] ?>">
        <div class="c-buy__buttons ">
            <div>
                <input aria-label="Количество товара" type="number"
                       class="amount_input amount_input_margin_none small"
                       name="amount_input"
                       data-max-count="<?php echo $data['maxCount'] ?>" value="1"/>
                <span class="orderUnit"></span>
            </div>
            <a rel="nofollow" class="addToCart product-buy"
               data-item-id="<?php echo $data["id"]; ?>">
                <?php echo $lang['addToOrder'] ?>
            </a>
        </div>
    </div>
</div>
