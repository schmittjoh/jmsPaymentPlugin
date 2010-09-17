<?php
/*
 * Copyright 2010 Johannes M. Schmitt
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


/**
 * Base actions for the jmsPaymentPlugin paypalDemo module.
 * 
 * @package     jmsPaymentPlugin
 * @subpackage  paypalDemo
 * @author      Johannes M. Schmitt <schmittjoh@gmail.com>
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class BasepaypalDemoActions extends sfActions
{
  /**
   * The initial action where the amount which should be processed by PayPal is
   * determined.
   * 
   * @param sfWebRequest $request
   * @return void
   */
  public function executeIndex(sfWebRequest $request)
  {
    $this->approvedPayments = Doctrine_Core::getTable('Payment')->createQuery('p')
                                ->leftJoin('p.Transactions t')
                                ->leftJoin('p.DataContainer d')
                                ->where('p.state = ? OR p.state = ?', array(Payment::STATE_APPROVED, Payment::STATE_DEPOSITING))
                                ->execute();
    
    $this->form = new mySimpleAmountForm();
    
    if ($request->hasParameter('simpleAmountForm'))
    {
      $this->form->bind($request->getParameter('simpleAmountForm'));
      
      if ($this->form->isValid())
      { 
        $data = new PaypalPaymentData();
        $data->subject = 'Test Payment #ABC344';
        
        $payment = Payment::create(
          $this->form->getValue('amount'), 
          $this->form->getValue('currency'),
          $data
        );
        
        $data->cancel_url = $this->context->getController()->genUrl(array(
          'module' => 'paypalDemo',
          'action' => 'cancelPayment',
          'reference' => $payment->id,
        ), true);
        $data->return_url = $this->context->getController()->genUrl(array(
          'module' => 'paypalDemo',
          'action' => 'completePayment',
          'reference' => $payment->id,
        ), true);
        $data->save();
        
        $this->redirect('paypalDemo/completePayment?reference='.$payment->id);
      }
    }
  }
  
  public function executeCaptureAmount(sfWebRequest $request)
  {
    $payment = Doctrine_Core::getTable('Payment')->createQuery('p')
                ->leftJoin('p.DataContainer d')
                ->leftJoin('p.Transactions t')
                ->where('p.id = ?', $request->getParameter('id'))
                ->fetchOne();
    $this->forward404Unless($payment);
    
    try 
    {
      if ($payment->hasOpenTransaction())
        $payment->performTransaction($payment->getOpenTransaction());
      else
        $payment->deposit();
    }
    catch (jmsPaymentException $e)
    {
      if ($e instanceof jmsPaymentApprovalExpiredException)
        return 'Expired';
        
      $this->message = $e->getMessage();
      
      return 'Error';
    }
  }
  
  public function executeCompletePayment(sfWebRequest $request)
  {
    $payment = Doctrine_Core::getTable('Payment')->createQuery('p')
                ->leftJoin('p.DataContainer d')
                ->leftJoin('p.Transactions t')
                ->where('p.id = ?', $request->getParameter('reference'))
                ->fetchOne();
    $this->forward404Unless($payment);
    
    try 
    {
      if ($payment->hasOpenTransaction())
      {
        $payment->performTransaction($payment->getOpenTransaction());
      }
      else
      {
        $payment->approve();
      }
    }
    catch (jmsPaymentException $e)
    {
      // for now there is only one action, so we do not need additional
      // processing here
      if ($e instanceof jmsPaymentUserActionRequiredException
          && $e->getAction() instanceof jmsPaymentUserActionVisitURL)
      {
        $this->amount = $payment->getOpenTransaction()->requested_amount;
        $this->currency = $payment->currency;
        $this->url = $e->getAction()->getUrl();
          
        return 'Redirect';
      }
      
      $this->error = $e->getMessage();
      
      return 'Error';
    }
  }
}
