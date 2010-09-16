<?php use_helper('Url') ?>

<script language="javascript" type="text/javascript">
  $(function() {
    $('#paymentDemoForm_method').change(function() {
      var selected = $(this).children(':selected').get(0).value;

      $(this).parent().parent().siblings('tr:contains("Method")').each(function() {
        if ($(this).is(':contains("'+selected+'")'))
        {
            $(this).show();
        }
        else
        {
            $(this).hide();
        }
      });
      
    }).trigger('change');
  });
</script>

<h2>Create New Payment</h2>

<?php echo $form->renderFormTag(url_for('paymentDemo/create')) ?>
<table>
  <?php echo $form ?>
  <tr>
    <td colspan="2"><input type="submit" name="paymentDemo_submit" value="Create Payment"/> <?php echo link_to('back to list', 'paymentDemo/index')?></td>
  </tr>
</table>
</form>