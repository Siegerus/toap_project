<?php

include_once CORE_LIB.'TinkoffMerchantAPI.php';

$api = new TinkoffMerchantAPI(
    $data['paramArray'][0]['value'],
    $data['paramArray'][1]['value'],
    $data['paramArray'][2]['value']
);

$params = array(
    'OrderId' => $data['id'],
    'Amount' => (float)round($data['summ'], 2)*100,
    'Description' => "Оплата заказа ".$data['orderNumber'],
    // 'DATA' => 'Email='.$data["orderInfo"][$data["id"]]["user_email"],
	'DATA' => [
		'Email' => $data['orderInfo'][$data['id']]['user_email']
	]
);

//if ($data['paramArray'][3]['value'] === 'true') {
	$items = array();
	$orderModel = new Models_Order();
	if (method_exists($orderModel, 'getCorrectOrderContent')) {
		$content = $orderModel->getCorrectOrderContent($data['orderInfo'][$data['id']]);
	} else {
		$content = unserialize(stripslashes($data['orderInfo'][$data['id']]['order_content']));
	}

	foreach ($content as $item) {
		$tmp = explode(PHP_EOL, $item['name']);

		$item = array(
			'Price'         => (float)round($item['price'], 2)*100,
			'Amount'        => (float)round($item['price'], 2)*100*$item['count'],
			'Quantity'      => $item['count'],
			'Tax'           => $data['paramArray'][5]['value'],
			'Name'          => strip_tags(htmlspecialchars_decode(MG::textMore($tmp[0], 125))),
			'PaymentMethod' => 'full_prepayment',
			'PaymentObject' => 'commodity',
		);

		$items[] = $item;
		unset($tmp);
	}

	if ($data['orderInfo'][$data['id']]['delivery_cost'] > 0) {
		$item = array(
			'Price'         => (float)round($data['orderInfo'][$data['id']]['delivery_cost'], 2)*100,
			'Amount'        => (float)round($data['orderInfo'][$data['id']]['delivery_cost'], 2)*100,
			'Quantity'      => 1,
			'Tax'           => $data['paramArray'][6]['value'],
			'Name'          => 'Доставка',
			'PaymentMethod' => 'full_prepayment',
			'PaymentObject' => 'service',
		);
		$items[] = $item;
	}

	$params['Receipt'] = array(
	    'EmailCompany' => $data['paramArray'][7]['value'],
	    'Email'        => $data['orderInfo'][$data['id']]['user_email'],
	    'Taxation'     => $data['paramArray'][4]['value'],
	    'Items'        => $items,
	);
//}
$params['NotificationURL'] = SITE.'/payment?id=18&pay=result';
$api->init($params);
?>

<?php if($api->paymentUrl != '') { ?>
<div class="payment-form-block">
<p>
 <em>
 <?php echo lang('paymentDiff1'); ?>"<a href="<?php echo SITE?>/personal"><?php echo lang('paymentDiff2'); ?></a>".
 </em>
</p>
<table>
<tr>
<td>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUAAAABQCAYAAABoMayFAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAYdEVYdFNvZnR3YXJlAHBhaW50Lm5ldCA0LjAuNWWFMmUAACS2SURBVHhe7Z0HeBXV+vUFklASLohiAcFCkV4UQcFeUJAPBPWKDRsqAioW7GLBLiqKFTt2xV5AsXfFAipeLIhYsRdQFIH9X7+ds8NmOCUhURK/dz3PenLOzJyZOYFZeftexWAwGAwGg8FgMBgMBoPBYDAYDAaDwWAwGAwGQy4456ovWbJkoNggtclgMBj+nZDQtRN7SfhqiNX0+ihxsfim2Ch1zNrigez3HzIYDIaqjpTgPSAu0es39OMCcfE999zjvv32W6fXH4nniPPEv8Q2qY8aDAZD1YJErqnYSizivQSttbjwlltuce+++642O3f11Ve7tdZay7Vv395999137s8//3TXXXed++GHHxDEO1LnQTgbixvpdXW2GQwGQ6VGSvAWIHT6+aP4teAaNWrkWrRo4XbaaScvfGy75JJLXNu2bV3Lli39tpEjR/KZheJs8c/UOW7UD3OLDQZD1YBE6zjE6/HHH3c33nijGzp0qDvuuOO8pTdhwgQvfgETJ050M2fOdHPnznXrr7++u/vuu924ceOCNfi5Dlk1dVqDwWCo/JBoFUi83n7rrbdcYWGh++9//+tdXUDc77333nNvv/225+zZs92CBd5gdGPHjnVdunRxvXr1QvwWa9PuqVMaDAZD1YEErM+iRYtcjx493BNPPOEuuOAC16ZNG9esWTO3xRZbuAEDBnh3eOONN3Zrr722GzhwoHv66afdNtts4y1Hff4dCWCN1OkMBoOh8kEi1VRidYB4tniheLS4jdhA/AZ3tm7duu6YY45x//vf/9zixRh2zru4CxcuROjc77//7u644w632WabuTXWWCMcc4LYSvupFzxT5NzHidtpe37q8gaDwfD3ISVCa6TelkDbSHY8JC7Bhf3mm2/cnDlz3Lx580pETrsWfvHFF377xx9/7E466STXuXNnV6tWLZeXl+dJNrh///6OshiE8Lnnniv5LD//+OMP9/3337tPP/3U/frrr/7c2ve1eIR2LyeE2r6ttlvW2GAwlA8Sk9VE6vROSm3y0PsB4rxPPvnEHXzwwW7dddd166yzjmvcuLG39q6//nq0ywPBOuecc1y9evVc37593a233urLYT744ANPBG/UqFGuefPm3h3mnAGIKduKiorceuut58/ftWtXd/vttwfr8SkdVj91W9xXXfEncZn7NRgMhjJBIlIzJTAIDWUpNVPbNxX/fPDBB731dsIJJ7jPP//cCxJJjC233NK/Dpg0aZIXsFdffTW1JT2w/nbbbTe3ySabcL3UVucee+wxL7BYmQjilClTfKKkd+/e/r2OfVSH5aXu7SA+w/2JO7HNYDAYygwJyKV//fWXGz16tBcksZ+0hezuh2Rv11xzTff888+jNx6UuFDnN23atNSWYlx00UWuWrVqPgFCATTubAzOjTgeeOCBrk6dOm611VYrcaEB+3fccUd3zTXXpLYUu8UkUfr168d+sK825+nnjGeffdY9+eSTbP9B29ZLfR2DwWAoHSQeXcRFZ5xxho/TvfnmmwjKdAnKLghQz5493bXXXsvLEvz000++2Dm2/gAWXIMGDbyQYrnVrl3bHXDAAd71veKKK1y7du28e7zvvvu6QYMGuY022siLXozx48e7Qw45JPWuGPPnz/duMZljHT9VHCQuwc1u2LChryvU20d0qBVRGwyG0kPCcfNnn33mhYnEBdlZBEfbF+B2Imi8j0GCApc4LnIGs2bNcjVr1nQ//vijf0+M76yzzvLZ3t13393hSoc6QOKJe+65p38doGu6ESNG+C6RJCitGTZsGMcsgvfdd59r2rSpL7w+9NBD2Y4r3CT1tcqDDuK15WB70fDPgj989YpfVihIvPk2T8O/EBIMYn9zr7zySm+R4QYjSptuuqm3tnAvSVgkoc/4ZAZWGa5uEEiEcdVVV3XPPPOMmzp1qvvll1/8diw1kiC4uxRGI5CdOnVy5557rt/P+UiWEBekdpBschL333+/22677XwnCRYmAow7jXg3adIkCOshqa9WHvQRXTnYWzT8M6gr9hcfF59lQwUAMW0qHifOFCvi/5ShMkKCUV/i88tRRx3lLSxAQTOihggifsTkEKgk2Pb66697F5n+3iBauLmcC3cYNxlLLQjg5ptv7rbddltfF4hlSZID0CdM1hdBxOpMh0ceecTHFlu1auX22Wcf31UCED7a6lLTZs5KfbXywASwamB18Ssx/N5fESsCe4p/iOG8h4qG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" alt="" /></td>
<td valign="middle">
<a class="default-btn" href="<?php echo $api->paymentUrl ?>"><?php echo lang('paymentPay'); ?></a>
</td>
</tr>
</table>
</div>
<?php } 
else { 
	echo lang('paymentTinkoffError'); 
} ?>