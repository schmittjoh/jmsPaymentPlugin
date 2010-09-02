
An error occurred while processing your payment. Please <?php echo link_to('try again', 'paypalDemo/index') ?>.

<?php if (sfConfig::get('sf_debug')): ?>
  <p style="border:1px solid #000; padding: 3px;">
    <?php echo $error ?>
  </p>
<?php endif ?>