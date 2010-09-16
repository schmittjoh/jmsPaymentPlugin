<?php use_helper('Url') ?>

<h2>Create New Payment</h2>

<?php echo $form->renderFormTag(url_for('paymentDemo/create')) ?>
<table>
  <?php echo $form ?>
  <tr>
    <td colspan="2"><input type="submit" name="paymentDemo_submit" value="Create Payment"/> <?php echo link_to('back to list', 'paymentDemo/index')?></td>
  </tr>
</table>
</form>