<?php use_helper('Number', 'Url') ?>

<!-- In a real world scenario, do not put your CSS/JS here -->
<style type="text/css">
  .table-transactions {
    width: 100%;
    margin: 0;
  }
  .td-transactions {
    padding: 0px;
  }
  .table-transactions th {
    border-top: none;
    padding: 0.25em;
  }
  .table-transactions th:first-child, .table-transactions td:first-child {
    border-left: none;
  }
  .table-transactions th:last-child, .table-transactions td:last-child {
    border-right: none;
  }
  .table-transactions tr:last-child > td {
    border-bottom: none;
  }
  
  .notice {
    background-color:#FFFDD0;
    border:1px dashed #000;
    width: 750px;
    padding:10px;
  }
</style>

<script type="text/javascript">
  $(function() {
    $('.tr-transactions').each(function() {
      $(this).hide();

      var a = document.createElement('a');
      a.href = 'javascript:void(0);';
      a.appendChild(document.createTextNode('view transactions'));      
      $(this).prev('tr').children('td:last').append(', ').append(a);

      $(a).click(function() {
        var tr = $(this).parent().parent().next();
        
        if (tr.is(':hidden'))
        {
          tr.show();
          $(this).text('hide transactions');
        }
        else
        {
          tr.hide();
          $(this).text('view transactions');
        }
      });
    });
  });
</script>

<?php if ($sf_user->hasFlash('notice')): ?>
<p class="notice"><?php echo $sf_user->getFlash('notice') ?></p>
<script language="javascript" type="text/javascript">
  setTimeout(function() {
    $('p.notice').fadeOut('slow');
  }, 10000);
</script>
<?php endif ?>

<h2>Payments</h2>

<table width="1000">
  <thead>
    <tr>
      <th width="140" align="left">ID</th>
      <th width="160" align="left">Amount</th>
      <th width="280" align="left">Method</th>
      <th width="80" align="left">Status</th>
      <th width="340" align="left">Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php if (count($payments) === 0): ?>
    <tr>
      <td colspan="4" align="center">There are no payments, yet. <?php echo link_to('Create Payment', 'paymentDemo/create') ?></td>
    </tr>
  <?php else: ?>
  <?php foreach ($payments as $payment): ?>
    <tr>
      <td><?php echo $payment->id ?></td>
      <td><?php echo format_currency($payment->target_amount, $payment->currency) ?></td>
      <td><?php echo substr(get_class($payment->DataContainer->getRawValue()), 0, -11) ?></td>
      <td><?php 
        switch ($payment->state)
        {
          case Payment::STATE_APPROVED:
            echo 'approved';
            break;
            
          case Payment::STATE_APPROVING:
            echo 'approving';
            break;
            
          case Payment::STATE_CANCELED:
            echo 'canceled';
            break;
          
          case Payment::STATE_COMPLETE:
            echo 'complete';
            break;
            
          case Payment::STATE_DEPOSITING:
            echo 'depositing';
            break;
            
          case Payment::STATE_EXPIRED:
            echo 'expired';
            break;
            
          case Payment::STATE_FAILED:
            echo 'failed';
            break;
            
          case Payment::STATE_NEW:
            echo 'new';
            break;
            
          default:
            echo 'unknown';
            break;
        }      
      ?></td>
      <td><?php
        $actions = array();
        $cancelAction = link_to('cancel', 'paymentDemo/cancel?id='.$payment->id);
        $approveAction = link_to('approve', 'paymentDemo/approve?id='.$payment->id);
        $depositAction = link_to('deposit', 'paymentDemo/deposit?id='.$payment->id);
        
        switch ($payment->state)
        {
          case Payment::STATE_NEW:
          case Payment::STATE_APPROVING:
            $actions[] = $approveAction;
            $actions[] = $cancelAction;
            break;
            
          case Payment::STATE_APPROVED:
          case Payment::STATE_DEPOSITING:
            $actions[] = $depositAction;
            break;
        }
        
        echo implode(', ', $actions);
      ?></td>
    </tr>
    <?php if (count($payment->Transactions) > 0): ?>
    <tr id="transactions_<?php echo $payment->id ?>" class="tr-transactions">
      <td>Transactions</td>
      <td colspan="4" class="td-transactions">
        <table class="table-transactions">
          <thead>
            <tr>
              <th>ID</th>
              <th>Type</th>
              <th>Requested Amount</th>
              <th>Processed Amount</th>
              <th>Status</th>
              <th>Response Code</th>
              <th>Reason Code</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payment->Transactions as $transaction): ?>
            <tr>
              <td><?php echo $transaction->id ?></td>
              <td><?php echo lcfirst(substr(get_class($transaction->getRawValue()), 9, -11))?>
              <td><?php echo format_currency($transaction->requested_amount, $transaction->currency)?></td>
              <td><?php echo format_currency($transaction->processed_amount, $transaction->currency)?></td>
              <td><?php 
                switch ($transaction->state)
                {
                  case FinancialTransaction::STATE_CANCELED:
                    echo 'canceled';
                    break;
                    
                  case FinancialTransaction::STATE_FAILED:
                    echo 'failed';
                    break;
                    
                  case FinancialTransaction::STATE_NEW:
                    echo 'new';
                    break;
                    
                  case FinancialTransaction::STATE_PENDING:
                    echo 'pending';
                    break;
                  
                  case FinancialTransaction::STATE_SUCCESS:
                    echo 'success';
                    break;
                    
                  default:
                    echo 'unknown';
                }
              ?></td>
              <td><?php echo $transaction->response_code ?></td>
              <td><?php echo $transaction->reason_code ?></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </td>
    </tr>
    <?php endif ?>
  <?php endforeach ?>
  <?php endif ?>
  </tbody>
</table>

<?php echo link_to('+ create new payment', 'paymentDemo/create') ?>