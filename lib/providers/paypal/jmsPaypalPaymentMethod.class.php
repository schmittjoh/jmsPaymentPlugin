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
 * Implements the payment method for Paypal
 * 
 * @package jmsPaymentPlugin
 * @subpackage providers
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsPaypalPaymentMethod extends jmsPaymentMethod
{
  const API_VERSION = '2.0';
  
  /**
   * @var CallerServices
   */
  private $_callerServices;
  
  /**
   * We need to make some adjustments to the loading procedure, 
   * so we can use the PayPal API library
   */
  public function __construct()
  {
    // unfortunately, the PayPal API was not made for PHP 5
    // so, we need to disable some error checks
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    
    // append the PayPal API base dir to the include path
    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR
                            .sfConfig::get('sf_lib_dir').'/vendor/PayPal/');
    
    require_once 'PayPal.php';
    require_once 'PayPal/Profile/Handler/Array.php';
    require_once 'PayPal/Profile/API.php';
  }
  
  /**
   * Approves the transaction
   * @see plugins/jmsPaymentPlugin/lib/method/jmsPaymentMethod#approve($data, $retry)
   */
  public function approve(jmsPaymentMethodData $data, $retry = false)
  {
    try 
    {
      $data = $this->_approve($data);
      return $data;
    }
    catch (Exception $e)
    {
      if (!$e instanceof jmsPaymentUserActionRequiredException && $retry)
        return $this->approve($data, false);
      else
        throw $e;
    }
  }
  
  /**
   * Approves the payment
   * 
   * @param jmsPaymentMethodData $data
   * @throws jmsPaymentUserActionRequiredException
   * @throws jmsPaymentCommunicationException
   * @return jmsPaymentMethodData
   */
  private function _approve(jmsPaymentMethodData $data)
  {
    if ($data->getValue('express_token') === null)
      $this->generateExpressUrl($data);
    else
      $this->verifyApproval($data);
  }
  
  /**
   * Contact the PayPal servers in order to verify the payment
   * @param jmsPaymentMethodData $data
   */
  private function verifyApproval(jmsPaymentMethodData $data)
  {
    $expressCheckoutDetailsRequest = PayPal::getType('GetExpressCheckoutDetailsRequestType');
    $expressCheckoutDetailsRequest->setToken($data->getValue('express_token'));

    $response = $this->getCallerServices()->GetExpressCheckoutDetails($expressCheckoutDetailsRequest);
    
    if (Pear::isError($response))
      throw new jmsPaymentCommunicationException(
        'Error while fetching express checkout details: '.$response->getMessage());
      
    if ($response->Ack !== 'Success')
      throw new jmsPaymentCommunicationException(
        'Express checkout details could not be retrieved: '.$response->Ack);
    
    $details = $response->getGetExpressCheckoutDetailsResponseDetails();
    $buyerInfo = $details->getPayerInfo();
    
    if ($this->verifiedAccountRequired() && $buyerInfo->PayerStatus !== 'verified')
      throw new jmsPaymentUnverifiedBuyerException('This PayPal account is not verified.');
    
    $data->setValue('payer_id', $buyerInfo->PayerID);
    
    $basicAmount = PayPal::getType('BasicAmountType');
    $basicAmount->setattr('currencyID', $data->getCurrency());
    $basicAmount->setval(number_format($data->getAmount(), 2));
      
    $paymentDetails = PayPal::getType('PaymentDetailsType');
    $paymentDetails->setOrderTotal($basicAmount);
    $paymentDetails->setOrderDescription($data->subject);

    $request = PayPal::getType('DoExpressCheckoutPaymentRequestDetailsType');
    $request->setToken($data->getValue('express_token'));
    $request->setPayerID($buyerInfo->PayerID);
    $request->setPaymentAction('Order');
    $request->setPaymentDetails($paymentDetails);
            
    $doExpressCheckoutPaymentRequest = PayPal::getType('DoExpressCheckoutPaymentRequestType');
    $doExpressCheckoutPaymentRequest->setVersion(self::API_VERSION);
    $doExpressCheckoutPaymentRequest->setDoExpressCheckoutPaymentRequestDetails($request);
        
    $response = $this->getCallerServices()
                 ->DoExpressCheckoutPayment($doExpressCheckoutPaymentRequest);

    if (Pear::isError($response))
    {
      $reasonCode = $response->getMessage();
      $data->setReasonCode($reasonCode);
      $e = new jmsPaymentCommunicationException(
        'Error while authorizing express checkout payment: '.$reasonCode);
      $e->setPaymentMethodData($data);
      
      throw $e;
    }
      
    if ($response->Ack !== 'Success')
    {
      $reasonCode = $this->extractErrors($response->Errors);
      $data->setReasonCode($reasonCode);
      $data->setResponseCode($response->Ack);
      $e = new jmsPaymentException('Payment could not be authorized: '.$reasonCode);
      $e->setPaymentMethodData($data);
      
      throw $e;
    }
                   
    $details = $response->getDoExpressCheckoutPaymentResponseDetails();
    $paymentInfo = $details->getPaymentInfo();
    $data->setValue('external_reference_number', $paymentInfo->TransactionID);
    $data->setProcessedAmount($data->getAmount());
  }
  
  /**
   * Generates the Express URL which is given to the user in order to approve
   * the payment
   * 
   * @param $data
   * @throws jmsPaymentUserActionRequiredException this is always thrown
   */
  private function generateExpressUrl(jmsPaymentMethodData $data)
  {
    $amount = PayPal::getType('BasicAmountType');
    $amount->setattr('currencyID', $data->getCurrency());
    $amount->setval(number_format($data->getAmount(), 2));
    
    $expressCheckout = PayPal::getType('SetExpressCheckoutRequestDetailsType');
    $expressCheckout->setNoShipping($data->getValue('no_shipping', 1));
    $expressCheckout->setCancelURL($data->getValue('cancel_url'));
    $expressCheckout->setReturnURL($data->getValue('return_url'));
    $expressCheckout->setPaymentAction('Order');
    $expressCheckout->setOrderDescription($data->getValue('subject'));
    $expressCheckout->setOrderTotal($amount);
    
    $expressCheckoutRequest = PayPal::getType('SetExpressCheckoutRequestType');
    $expressCheckoutRequest->setVersion(self::API_VERSION);
    $expressCheckoutRequest
      ->setSetExpressCheckoutRequestDetails($expressCheckout);
    
    $request = $this->getCallerServices()
                ->SetExpressCheckout($expressCheckoutRequest);
                
    if (Pear::isError($request))
      throw new jmsPaymentCommunicationException(
        'Error when retrieving the express URL: '.$request->getMessage()
      );
      
    if ($request->Ack !== 'Success')
    {
      $reasonCode = $this->extractErrors($request->Errors);
      $data->setReasonCode($reasonCode);
      $e = new jmsPaymentException('Error when retrieving the express url: '.$reasonCode);
      $e->setPaymentMethodData($data);
      
      throw $e;
    }
      
    $host = !$this->isDebug() ? 
              'www.paypal.com' : 'www.sandbox.paypal.com';

    $data->setProcessedAmount($data->getAmount());
    $data->setValue('express_token', $request->Token);
    $data->setValue('express_url', sprintf(
        'https://%s/cgi-bin/webscr?cmd=_express-checkout&token=%s',
        $host, $request->Token
      )
    );
    
    // throw 
    $exception = new jmsPaymentUserActionRequiredException(
      new jmsPaymentUserActionVisitURL($data->getValue('express_url'))
    );
    $exception->setPaymentMethodData($data);
    throw $exception;
  }
  
  /**
   * Deposits a transaction
   * @throws jmsPaymentApprovalExpiredException
   * @throws jmsPaymentCommunicationException
   * @see plugins/jmsPaymentPlugin/lib/method/jmsPaymentMethod#deposit($data, $retry)
   */
  public function deposit(jmsPaymentMethodData $data, $retry = false)
  {
    $amount = PayPal::getType('BasicAmountType');
    $amount->setattr('currencyID', $data->getCurrency());
    $amount->setval(number_format($data->getAmount(), 2)); 

    $captureRequest = Paypal::getType('DoCaptureRequestType');
    $captureRequest->setAmount($amount);
    $captureRequest->setAuthorizationId($data->getValue('external_reference_number'));
    $captureRequest->setCompleteType('Complete');
    if ($data->hasValue('note'))
      $captureRequest->setNote($data->getValue('note'));
      
    $result = $this->getCallerServices()->DoCapture($captureRequest);
    
    if (Pear::isError($result))
      throw new jmsPaymentCommunicationException(
        'Error while capturing payment: '.$result->getMessage());
    
    if ($result->Ack !== 'Success')
      throw new jmsPaymentCommunicationException('Error '.$result->Ack.' while capturing.');
    
    $response = $result->getDoCaptureResponseDetails();
    $paymentInfo = $response->getPaymentInfo();
    
    $data->setResponseCode($paymentInfo->PaymentStatus);
    $data->setProcessedAmount($data->getAmount());
    
    // process the payment status
    switch ($paymentInfo->PaymentStatus)
    {
      case 'Expired':
        $e = new jmsPaymentApprovalExpiredException();
        $e->setPaymentMethodData($data);
        throw $e;
      
      case 'Completed':
        return $data;
        
      case 'Pending':
        $e = new jmsPaymentException('Payment is still pending; reason: '.$paymentInfo->PendingReason);
        $data->setReasonCode($paymentInfo->PendingReason);
        $e->setPaymentMethodData($data);
        throw $e;
        
      default:
        // TODO: Some more processing as to what went wrong exactly
        $e = new jmsPaymentException('Payment could not be completed. Status: '.$paymentInfo->PaymentStatus);
        $e->setPaymentMethodData($data);
        throw $e; 
    }
  }
  
  protected function extractErrors($errors)
  {
    if (is_array($errors))
    {
      $messages = '';
      
      foreach ($errors as $error)
        $messages .= $error->LongMessage."\n";
        
      return trim($messages);
    }
    
    return $errors->LongMessage;
  }
  
  /**
   * This can be overwritten by sub classes if they want to change the way
   * how the username is determined. However, the default should be sufficient
   * for most applications.
   * 
   * @return string
   */
  protected function getUsername()
  {
    $config = sfConfig::get('app_jmsPaymentPlugin_paypal');
    if (!array_key_exists('username', $config))
      throw new RuntimeException('You must set a PayPal username.');
      
    return $config['username'];
  }
  
  /**
   * This method can be overwritten by child classes if you want to change the way
   * how the signature is determined.
   * 
   * @return string
   */
  protected function getSignature()
  {
    $config = sfConfig::get('app_jmsPaymentPlugin_paypal');
    if (!array_key_exists('signature', $config))
      throw new RuntimeException('You must set a PayPal signature.');
      
    return $config['signature'];
  }
  
  /**
   * This method can be overwritten by child classes if you want to change the way
   * how the password is determined.
   * 
   * @return string
   */
  protected function getPassword()
  {
    $config = sfConfig::get('app_jmsPaymentPlugin_paypal');
    if (!array_key_exists('password', $config))
      throw new RuntimeException('You must set a PayPal password.');
      
    return $config['password'];
  }
  
  /**
   * Whether people need a verified PayPal account for payments
   * @return boolean
   */
  protected function verifiedAccountRequired()
  {
    $config = sfConfig::get('app_jmsPaymentPlugin_paypal');
    
    return isset($config['verified_account_required'])
           && $config['verified_account_required'] === true;
  }
  
  /**
   * Creates a CallerServices object with our credentials
   * 
   * @throws RuntimeException if the API Caller could not be initialized
   * @return CallerServices
   */
  public function getCallerServices()
  {
    if ($this->_callerServices === null)
    {
      $username = $this->getUsername();
      $signature = $this->getSignature();
      $password = $this->getPassword();
      $environment = $this->isDebug() ? 'Sandbox' : 'Live';
      
      $handler = ProfileHandler_Array::getInstance(array(
        'username'           => $username,
        'certificateFile'    => null,
        'signature'          => $signature,
        'subject'            => null,
        'environment'        => $environment,
      ));
      
      $profile = APIProfile::getInstance($username, $handler);
      $profile->setAPIPassword($password);
                  
      $caller = PayPal::getCallerServices($profile);
      
      // if we are in debug mode, ignore any invalid SSL certificates
      // TODO: Check if we also need this in production
      if ($this->isDebug())
      {
        $caller->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 0);
        $caller->setOpt('curl', CURLOPT_SSL_VERIFYHOST, 0);
      }
        
      if (PayPal::isError($caller))
        throw new RuntimeException('The API Caller could not be initialized: '
                                   .$caller->getMessage());
        
      $this->_callerServices = $caller;
    }
        
    return $this->_callerServices;
  }
}