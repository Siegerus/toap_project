<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="675">
  <tbody>
  <tr>
      <td valign="top">
      <h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">
          Здравствуйте, <?php echo $data['fio'] ?>!
        </h1>

<?php // echo print_r($data,1); ?>

        <div style="font-size:12px;line-height:16px;margin:0;">
          Ваша заявка <b>№<?php echo $data['orderNumber'] ?></b> успешно оформлена.
      <p class="confirm-info" style="font-size:12px;margin:0 0 10px 0">
      <br>
          Если у Вас возникнут вопросы — их можно задать по почте:
          <a href="mailto:<?php echo MG::getOption('adminEmail'); ?>" style="color:#1E7EC8;" target="_blank"><?php echo MG::getOption('adminEmail'); ?></a>
          или по телефону
      <span>
            <span class="js-phone-number highlight-phone"><?php echo $data['shopPhone'] ?></span>
          </span>
        </div>
      </td>
    </tr>
    <tr>
      <td>
          <h2 style="font-size:18px;font-weight:normal;margin:0;">Ваша заявка №<?php echo $data['orderNumber'] ?> <small> (<?php echo date('d.m.Y H:i', strtotime($data['formatedDate'])) ?>)</small></h2>
      </td>
    </tr>
    <tr>
      <td>
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
        </table>
            

        <p style="font-size:12px;margin:0 0 10px 0">

        </p>
      </td>
    </tr>
    <tr>
      <td bgcolor="#EAEAEA" align="center" style="background:#EAEAEA;text-align:center;">

  <center>
    <p style="font-size:12px;margin:0;">
      Спасибо за обращение! Следите за новостями на нашем <a href="<?php echo SITE ?>">сайте</a>!
    </p>
  </center>
</td>
</tr>

<?php if(!empty($data['adminMail'])):?>
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
<?php endif;?>

</tbody></table>

