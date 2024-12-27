<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="675">
  <tbody>
     <tr>
      <td>
        <h3 style="font-size:18px;font-weight:normal;margin:0;">
            На сайте <?php echo $data['siteName'] ?> магазина «<strong><?php echo $data['shopName'] ?></strong>» создан заказ №<?php echo $data['orderNumber'] ?>
          <small>(<?php echo date('d.m.Y H:i', strtotime($data['formatedDate'])) ?>)</small></h3>
      </td>
    </tr>
    <tr>
      <td>
           <span>Информация о заказе:</span>
        <br>
        <table cellspacing="0" cellpadding="0" border="0" width="675" style="border:1px solid #EAEAEA;">

          <thead>
            <tr>
              <th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Услуга</th>
              <th align="right" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">стоимость</th>
            </tr>
          </thead>
          <tbody bgcolor="#F6F6F6">
            <?php if (!empty($data['productPositions']) || $data['adminOrder']) : ?>                  
              <?php foreach ($data['productPositions'] as $product) : ?>
                <?php $product['property'] = htmlspecialchars_decode(str_replace('&amp;', '&', $product['property'])); ?>
                <tr>
                  <td style="font-size:13px;padding:5px 9px;"><?php echo $product['name'].$product['property'] ?></td>
                   <td style="font-size:13px;padding:5px 9px;" align="right"><?php echo MG::numberFormat($product['price']).' '.$data['currency'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody> 

          <tr>
            <td colspan="3" align="right" style="padding:5px 9px 0 9px;font-size: 13px;">
              стоимость товаров                    </td>
            <td align="right" style="padding:5px 9px 0 9px;font-size: 13px;font-weight:bold;">
              <span><?php echo  MG::numberFormat($data['result']).' '.$data['currency'] ?></span>                    </td>
          </tr>
         
     
        </table>    


            <p style="padding:2px 9px;font-size: 13px;"> <strong>Комментарий покупателя:</strong>
              <?php echo $data['userComment'];?>
            </p>

        <p style="font-size:12px;margin:0 0 10px 0">

        </p>
      </td>
    </tr>
    <tr>
   <td bgcolor="" align="left" style="background:#F5F3C6;">
      <p style="font-size:11px;margin:0;">
        ip пользователя: <b><?php echo $data['ip']?></b><br/>
        Покупатель сделал этот заказ после перехода из:  <b><?php echo $data['lastvisit']?> </b><br/>
        Покупатель впервые пришел к нам на сайт из:  <b><?php echo $data['firstvisit']?> </b><br/>
        Покупатель использовал купон: <b><?php echo $data['couponCode']?> </b><br/>
      </p>
  </td>
  </tr>
  </tbody>
</table>

