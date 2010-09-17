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
 * Base actions for the jmsPaymentPlugin paymentDemo module.
 * 
 * @package     jmsPaymentPlugin
 * @subpackage  paymentDemo
 * @author      Johannes M. Schmitt <schmittjoh@gmail.com>
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class BasepaymentDemoActions extends sfActions
{
  public function executeDeposit(sfWebRequest $request)
  {
    $payment = $this->getPaymentFromRequest($request);
    $this->forward404Unless($payment);
    
    try
    {
      if ($payment->hasOpenTransaction())
      {
        $transaction = $payment->getOpenTransaction();
        if (!$transaction instanceof FinancialDepositTransaction)
          throw new LogicException('This payment has another pending transaction.');
          
        $payment->performTransaction($transaction);
      }
      else
        $payment->deposit();
    }
    catch (jmsPaymentException $e)
    {
      $this->error = $e->getMessage();
      
      return 'Error';
    }
    
    $this->getUser()->setFlash('notice', 'The payment was deposited successfully.');
    $this->redirect('paymentDemo/index');    
  }
  
  public function executeApprove(sfWebRequest $request)
  {
    $payment = $this->getPaymentFromRequest($request);            
    $this->forward404Unless($payment);
    
    try 
    {
      if ($payment->hasOpenTransaction())
      {
        $transaction = $payment->getOpenTransaction();
        if (!$transaction instanceof FinancialApproveTransaction)
          throw new LogicException('This payment has another pending transaction.');
          
        $payment->performTransaction($transaction);
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
    
    $this->getUser()->setFlash('notice', 'The payment was approved successfully.');
    $this->redirect('paymentDemo/index');
  }
  
  public function executeCreate(sfWebRequest $request)
  {
    $this->form = new PaymentDemoForm();
    
    if ($request->hasParameter('paymentDemoForm'))
    {
      $this->form->bind($request->getParameter('paymentDemoForm'));
      
      if ($this->form->isValid())
      {
        $data = $this->form->getPaymentData();
        $data->subject = 'Test #ABC123';
        $data->internal_reference_number = 'ABC123';
        
        // if this is used in a production system, I would recommend moving this to
        // the PaypalPaymentDataForm directly to keep your action as generic as possible
        if ($data instanceof PaypalPaymentData)
        {
          $data->return_url = $this->context->getController()->genUrl(array(
            'module' => 'paymentDemo',
            'action' => 'approve',
          ), true);
          
          $data->cancel_url = $this->context->getController()->genUrl(array(
            'module' => 'paymentDemo',
            'action' => 'cancel',
          ), true);
        }
        
        $payment = Payment::create(
          $this->form->getValue('amount'),
          $this->form->getValue('currency'),
          $data
        );
        
        $this->getUser()->setFlash('notice', 'Your payment has been created successfully.');
        $this->redirect('paymentDemo/index');
      }
    }
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->payments = Doctrine_Core::getTable('Payment')->createQuery('p')
                        ->innerJoin('p.DataContainer d')
                        ->leftJoin('p.Transactions t')
                        ->orderBy('p.id DESC')
                        ->execute();
  }
  
  protected function getPaymentFromRequest(sfWebRequest $request)
  {
    if ($request->hasParameter('token'))
      return Doctrine_Core::getTable('Payment')->createQuery('p')
              ->innerJoin('p.DataContainer d WITH d.express_token = ?', $request->getParameter('token'))
              ->leftJoin('p.Transactions t')
              ->fetchOne(); 
    
    else
      return Doctrine_Core::getTable('Payment')->createQuery('p')
              ->leftJoin('p.DataContainer d')
              ->leftJoin('p.Transactions t')
              ->where('p.id = ?', $request->getParameter('id'))
              ->fetchOne();
    
    return false;
  }
}
