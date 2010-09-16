<?php

require_once dirname(__FILE__).'/../lib/BasepaymentDemoActions.class.php';

/**
 * paymentDemo actions.
 * 
 * @package    jmsPaymentPlugin
 * @subpackage paymentDemo
 * @author     Johannes M. Schmitt <schmittjoh@gmail.com>
 * @version    SVN: $Id: actions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
class paymentDemoActions extends BasepaymentDemoActions
{
  public function preExecute()
  {
    $this->setDemoLayout();
  }

  private function setDemoLayout()
  {
    $this->setLayout(dirname(__FILE__).'/../templates/demoLayout');
  }
}
