
<div class="section-<?php echo $pluginName ?>">

    <!-- Устанавливаются базовые натсройки вывода формы заказа "одни кликом" -->
    <div class="widget-body">
        <div class="wrapper-buyclick-setting">
            <div class="widget-table-action base-settings">
                <div class="widget-body buyclick-editor"><!-- Содержимое окна, управляющие элементы -->

                    <h2 class="widget-header"><?php echo $lang['BUY_CLICK_TITLE'] ?></h2>

                    <ul class="list-option">
                        <li><label class="one-line"><span><?php echo $lang['BUY_CLICK_NAME'] ?>:</span> <input type="checkbox" name="name" value="<?php echo $options["name"] ?>" <?php echo ($options["name"] && $options["name"] != 'false') ? 'checked=cheked' : '' ?>></label></li>
                        <li><label class="one-line"><span><?php echo $lang['BUY_CLICK_PHONE'] ?>:</span> <input type="checkbox" name="phone" value="<?php echo $options["phone"] ?>" <?php echo ($options["phone"] && $options["phone"] != 'false') ? 'checked=cheked' : '' ?>></label></li>
                        <li><label class="one-line"><span><?php echo $lang['BUY_CLICK_MAIL'] ?>:</span> <input type="checkbox" name="email" value="<?php echo $options["email"] ?>" <?php echo ($options["email"] && $options["email"] != 'false') ? 'checked=cheked' : '' ?>></label></li>
                        <li><label class="one-line"><span><?php echo $lang['BUY_CLICK_ADDRESS'] ?>:</span> <input type="checkbox" name="address" value="<?php echo $options["address"] ?>" <?php echo ($options["address"] && $options["address"] != 'false') ? 'checked=cheked' : '' ?>></label></li>
                        <li><label class="one-line"><span><?php echo $lang['BUY_CLICK_COMMENT'] ?>:</span> <input type="checkbox" name="comment" value="<?php echo $options["comment"] ?>" <?php echo ($options["comment"] && $options["comment"] != 'false') ? 'checked=cheked' : '' ?>></label></li>
                        <li><label class="one-line"><span><?php echo $lang['BUY_CLICK_IMG'] ?>:</span> <input type="checkbox" name="product" value="<?php echo $options["product"] ?>" <?php echo ($options["product"] && $options["product"] != 'false') ? 'checked=cheked' : '' ?>></label></li>
                        <li><label class="one-line"><span><?php echo $lang['BUY_CLICK_CAPCHA'] ?>:</span> <input type="checkbox" name="capcha" value="<?php echo $options["capcha"] ?>" <?php echo ($options["capcha"] && $options["capcha"] != 'false') ? 'checked=cheked' : '' ?>></label></li>

                        <li><span><?php echo $lang['BUY_CLICK_DELIVERY'] ?>:</span>
                            <select name="delivery">
                                <?php
                                foreach ($entity as $deliveries) {
                                  $selected = '';
                                  if ($deliveries['id'] == $options["delivery"]) {
                                    $selected = 'selected="selected"';
                                  }
                                  echo '<option value="'.$deliveries['id'].'" '.$selected.' >'.$deliveries['description'].'</option>';
                                }
                                ?>
                            </select>
                        </li>

                        <li><span><?php echo $lang['BUY_CLICK_PAYMENT'] ?>:</span>
                            <select name="payment">
                                <?php
                                foreach ($payment as $paymentMethod) {
                                  $selected = '';
                                  if ($paymentMethod['id'] == $options["payment"]) {
                                    $selected = 'selected="selected"';
                                  }
                                  echo '<option value="'.$paymentMethod['id'].'" '.$selected.' >'.$paymentMethod['name'].'</option>';
                                }
                                ?>
                            </select>
                        </li>

                        <li><span class="textarea-text"><?php echo $lang['BUY_CLICK_ORDER_TITLE'] ?>:</span><textarea type="text" name="header"><?php echo $options["header"]; ?></textarea></li>
                        <li><span class="textarea-text"><?php echo $lang['BUY_CLICK_BTN_TITLE'] ?>:</span><textarea type="text" name="button"><?php echo $options["button"]; ?></textarea></li>
                        <li><button class="save-button tool-tip-bottom button success fl-right" data-id="" title="<?php echo $lang['SAVE_MODAL'] ?>"><!-- Кнопка действия -->
                                <span><i class="fa fa-floppy-o" aria-hidden="true"></i> <?php echo $lang['SAVE_MODAL'] ?></span>
                            </button></li>
                    </ul>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
</div>    




