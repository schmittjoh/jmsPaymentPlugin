<?php use_helper('Number') ?>
Amount: <?php echo format_currency($amount, $currency) ?>
<br/><br/>
Please visit <?php echo link_to($url, $url) ?> to complete the payment. 