<?php
if (EDITION == 'gipermarket'|| EDITION == 'saas') {
    $opFieldsM = new Models_OpFieldsProduct($data['id']);
    $fields = $opFieldsM->get();
    foreach ($fields as $key => $value) {
        if ($value['active'] == 0) continue;
        if (!empty($data['variant'])) {
            echo '<div class="product-opfield">'
              . '<span class="product-opfield__name">'
              . $value['name'] . ': '
              . '</span>'
              . '<span class="product-opfield__value">'
              . $value['variant'][$data['variant']]['value']
              . '</span>'
              . '</div>';
        } else {
            if (!empty($value['value'])) {
                echo '<div class="product-opfield">'
                  . '<span class="product-opfield__name">'
                  . $value['name'] . ': '
                  . '</span>'
                  . '<span class="product-opfield__value">'
                  . $value['value']
                  . '</span>'
                  . '</div>';
            }
        }
    }
}
