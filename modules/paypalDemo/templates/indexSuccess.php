<?php use_helper('Number') ?>
<h2>Approved Payments</h2>
<table>
<?php if (count($approvedPayments) > 0):?>
  <thead>
    <tr>
      <td>ID</td>
      <td>Amount</td>
      <td>Actions</td>
    </tr>
  </thead>
  <tbody>
<?php foreach ($approvedPayments as $payment): ?>
    <tr>
      <td><?php echo $payment->id ?></td>
      <td><?php echo format_currency($payment->target_amount, $payment->currency) ?></td>
      <td><?php echo link_to('Capture Amount', 'paypalDemo/captureAmount?id='.$payment->id)?></td>
    </tr>
<?php endforeach ?>
  </tbody>
<?php else: ?>
  <tr>
    <td>There are no approved payments, yet.</td>
  </tr>
<?php endif ?>
</table>

<h2>Create a New Payment</h2>
Please set the amount that should be processed by PayPal.
<?php echo $form->renderFormTag(url_for('paypalDemo/index')) ?>
  <table> 
    <?php echo $form ?>
    <tr>
      <td colspan="2"><input type="submit" name="set_amount" value="Set Amount"/></td>
    </tr>
  </table>
</form>