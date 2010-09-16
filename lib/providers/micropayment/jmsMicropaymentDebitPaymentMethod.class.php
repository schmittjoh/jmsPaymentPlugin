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
 * Implements the payment method for Micropayment's debit payment.
 * 
 * This class expects the Micropayment-Service-Client (MSC) in 
 * lib/vendor/micropayment.
 * 
 * @package jmsPaymentPlugin
 * @subpackage providers
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsMicropaymentDebitPaymentMethod extends jmsMicropaymentPaymentMethod
{
  const SERVICE_INTERFACE = 'IMcpDebitService_v1_3';
  const NVP_URL = 'https://webservices.micropayment.de/public/debit/v1.4/nvp/';
  
  public function approve(jmsPaymentMethodData $data, $retry = false)
  {
    $customerId = $this->createCustomer();
    
    try 
    {
      // set bank account data
      $result = $this->getDispatcher()->bankaccountSet(
        $this->getAccessKey(),
        $this->getTestMode(),
        $customerId,
        $data['bank_country'],
        $data['bank_code'],
        $data['account_number'],
        $data['account_holder']
      );

      if ($result['barStatus'] !== 'ALLOWED')
      {
        $data->setResponseCode($result['barStatus']);
        $data->setReasonCode('Bank account is not allowed.');
        $e = new jmsPaymentException('This bank account is blocked.');
        $e->setPaymentMethodData($data);
        
        throw $e;
      }
      
      // create debit payment
      $result = $this->getDispatcher()->sessionCreate(
        $this->getAccessKey(),
        $this->getTestMode(),
        $customerId,
        '',
        $this->getProject($data),
        $this->getProjectCampaign($data),
        '',
        '',
        $data->getAmount(),
        $data->getCurrency(),
        $data['subject'],
        $data['payment_text'] === null? $data['subject'] : $data['payment_text'],
        $data['ip'],
        null
      );
      
      $data->setResponseCode($result['status']);
      $data->setProcessedAmount($data->getAmount());
      $data['external_reference_number'] = $result['sessionId'];
    }
    catch (Exception $e)
    {
      $data->setReasonCode($e->getMessage());
      $e = jmsPaymentException::fromException($e);
      $e->setPaymentMethodData($data);
      
      throw $e;
    }
  }
  
  public function deposit(jmsPaymentMethodData $data, $retry = false)
  {
    try 
    {
      $result = $this->getDispatcher()->sessionApprove(
        $this->getAccessKey(),
        $this->getTestMode(),
        $data['external_reference_number']
      );
      
      $data->setResponseCode($result['status']);
      
      if ($result['status'] === 'APPROVED')
        $data->setProcessedAmount($data->getAmount());
    }
    catch (Exception $e)
    {
      $data->setReasonCode($e->getMessage());
      $e = jmsPaymentException::fromException($e);
      $e->setPaymentMethodData($data);
      
      throw $e;
    }
  }
  
  /** @inheritDoc */
  public final function getServiceInterface()
  {
    return self::SERVICE_INTERFACE;
  }
  
  /** @inheritDoc */
  public final function getNvpUrl()
  {
    return self::NVP_URL;
  }
}