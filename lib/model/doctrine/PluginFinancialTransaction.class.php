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
 * PluginFinancialTransaction
 * 
 * @package    prometheus
 * @subpackage payment
 * @author     Johannes M. Schmitt <schmittjoh@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginFinancialTransaction extends BaseFinancialTransaction
{
  const STATE_NEW = 1;
  const STATE_PENDING = 2;
  const STATE_CANCELED = 3;
  const STATE_FAILED = 4;
  const STATE_SUCCESS = 5;
  
  // these types must match with the 'keyValue' values defined in the 
  // schema.yml file
  const TYPE_APPROVE = 'approve';
  const TYPE_DEPOSIT = 'deposit';
  const TYPE_REVERSE_APPROVAL = 'reverseApproval';
  const TYPE_REVERSE_DEPOSIT = 'reverseDeposit';
  
  /**
   * An array with payment methods
   * @var array
   */
  private static $_paymentMethods = array();
  
  /**
   * Possible End States: cancelled, failed, success
   * @var array An array of allowed state transitions
   */
  private static $_allowedStateTransitions = array(
    self::STATE_CANCELED => array(),
    self::STATE_FAILED => array(),
    self::STATE_NEW => array(
      self::STATE_CANCELED,
      self::STATE_PENDING,
    ),
    self::STATE_PENDING => array(
      self::STATE_FAILED,
      self::STATE_SUCCESS,
    ),
    self::STATE_SUCCESS => array()
  );
  
  /**
   * An array of allowed currency codes
   * @var array
   */
  private static $_allowedCurrencies;
  
  /**
   * Whether this transaction is executed for the first time.
   * It makes only sense to check this variable during the execution process,
   * namely inside of execute(), and doExecute().
   * 
   * @var boolean
   */
  private $_isFirstExecution = false;
  
  /**
   * Used to verify that the model is used as intended to avoid problems
   * which will otherwise only be discovered later.
   * 
   * @var boolean
   */
  private $_iKnowWhatImDoing = false;
  
  /**
   * Creates a financial transaction
   * 
   * @param string $type
   * @param Payment $payment 
   * @param mixed $amount anything that can be converted to a float correctly
   * TODO: Allow a financial transaction to have a dedicated data container. 
   *       This is relevant in cases, where you want to apply different 
   *       payment methods to the same payment. For example, if the processed
   *       amount of the transaction is smaller than the requested amount, you
   *       might want to allow the customer to pay the remaining amount using a
   *       different payment method. A problem here is that the data container
   *       must be passed on from approve transaction to the deposit transaction
   *       manually. 
   * 
   * @return FinancialTransaction
   */
  public final static function create($type, Payment $payment, $amount = null, 
     $currency = null)
  {
    $class = sprintf('Financial%sTransaction', $type);
    
    // validate currency
    if ($currency === null)
      $currency = $payment->currency;
    if (!in_array($currency, self::getAllowedCurrencies(), true))
      throw new InvalidArgumentException('$currency has an invalid value.');    
    
    // this is only for convenience, since in most cases you won't need to make
    // more than one transaction of each type
    if ($amount === null)
    {
      if ($type === self::TYPE_REVERSE_APPROVAL)
        $amount = $payment->approved_amount;
      else if ($type === self::TYPE_REVERSE_DEPOSIT)
        $amount = $payment->deposited_amount;
      else
        $amount = $payment->getPendingAmount();
      
      // we need to convert this amount using the current rate in case the 
      // currencies are not equal
      $amount = Doctrine_Core::getTable('Currency')->convertAmount(
        $amount, $payment->currency, $currency
      );
    }
    
    // verify that the payment has no open transactions
    if ($payment->hasOpenTransaction() === true)
      throw new RuntimeException(
        'Each payment can only have one open transaction at a time.');
      
    $transaction = new $class;
    $transaction->_iKnowWhatImDoing = true;
    $transaction->setPaymentContainer($payment);
    $transaction->currency = $currency; 
    $transaction->requested_amount = $amount;
    $transaction->save();
    
    return $transaction;
  }
  
  /**
   * Returns map of allowed state transitions
   * @return array
   */
  public final static function getAllowedStateTransitions()
  {
    return self::$_allowedStateTransitions;
  }
  
  /**
   * Returns an array of allowed currencies. This should be considered immutable.
   * @return array
   */
  public final static function getAllowedCurrencies()
  {
    if (self::$_allowedCurrencies === null)
    {
      $def = Doctrine_Core::getTable('FinancialTransaction')
              ->getDefinitionOf('currency');
      self::$_allowedCurrencies = &$def['values'];
    }
    
    return self::$_allowedCurrencies;
  }
  
  /**
   * Whether the given state is a final state.
   * @param integer $state
   * @return boolean
   */
  public final static function isFinalState($state)
  {
    if (!isset(self::$_allowedStateTransitions[$state]))
      throw new InvalidArgumentException('Invalid state: '.var_export($state, true));
      
    return count(self::$_allowedStateTransitions[$state]) === 0;
  }
  
  /**
   * Make sure is an integer even when the payment is coming from the database
   * @return integer
   */
  public final function getState()
  {
  	return intval($this->_get('state'));
  }
  
  /**
   * Sets the state of this transaction. There should never be a case where you
   * need to call this method manually. If you temper with the transaction's
   * state, this might cause huge problems later on.
   * 
   * @param integer $state
   */
  public final function setState($state)
  {
    // do nothing if the state is not changed
    if ($state === $this->state)
      return;
    
    // verify that the state change is allowed
    if (!in_array($state, self::$_allowedStateTransitions[$this->state], true))
        throw new InvalidArgumentException('This target state is not allowed.');
    
    $this->_set('state', $state);
  }
  
  /**
   * Sets the requested amount of this transaction
   * @param mixed $amount Any value that is correctly converted to a float
   */
  public function setRequestedAmount($amount)
  {      
    if ($this->state !== self::STATE_NEW)
      throw new RuntimeException(
        'The requested amount cannot be changed on not NEW transactions.');

    if (jmsPaymentNumberUtil::compareFloats($amount, 0.0) <= 0)
      throw new InvalidArgumentException('$amount cannot be smaller or equal to 0.');

    $this->_set('requested_amount', $amount);
  }
  
  /**
   * Prevent instances which have been created without the static constructors
   * from being saved.
   * @param Doctrine_Event $event
   */
  public final function preInsert($event)
  {
    if ($this->_iKnowWhatImDoing === false)
      throw new RuntimeException(
        'Please use the static constructors to create financial transactions.');
  }
  
  /**
   * Sets the payment container, and saves the transaction
   * @param Payment $payment
   */
  private function setPaymentContainer(Payment $payment)
  {
    if ($this->exists() !== false)
      throw new RuntimeException(
        'A payment container can only be set on transient transactions.');
    
    if ($this->canBePerformedOn($payment) === false)
      throw new LogicException(
        sprintf('"%s" cannot be performed on this payment.', get_class($this)));
      
    $this->Payment = $payment;
    $payment->Transactions[] = $this;
  }
  
  /**
   * Whether this transaction is executed for the first time.
   * A transaction can be executed multiple times if a user action is required.
   * 
   * @return boolean
   */
  protected final function isFirstExecution()
  {
    return $this->_isFirstExecution;
  }
  
  /**
   * Whether this transaction can be performed on the given payment
   * 
   * @param Payment $payment
   * @return boolean
   */
  protected function canBePerformedOn(Payment $payment) 
  {
    throw new LogicException('The method "canBePerformedOn()" must be implemented by sub-classes.');
  }
  
  /**
   * Executes this transaction. You do not need to call this method manually.
   * Call Payment::performTransaction($transaction) instead.
   * 
   * @throws jmsPaymentCommunicationException
   * @throws jmsPaymentFinancialException
   * @throws jmsPaymentFunctionNotSupportedException
   * @throws jmsPaymentTimeoutException
   * @throws jmsPaymentUserActionRequiredException
   * @throws jmsPaymentApprovalExpiredException
   * @return void
   */
  public final function execute()
  {
    if ($this->state !== self::STATE_NEW 
        && $this->state !== self::STATE_PENDING)
      throw new RuntimeException(
        'execute() can only be called on new, or pending transactions.');
      
    if ($this->state === self::STATE_NEW)
    {
      $this->state = self::STATE_PENDING;
      $this->_isFirstExecution = true;
    }
    else
      $this->_isFirstExecution = false;
      
    try 
    {
      $data = $this->doExecute();
      $this->updateFromPaymentMethodData($data);
      $this->state = self::STATE_SUCCESS;
      $this->save();
    }
    catch (jmsPaymentException $e)
    {
      // anything except a jmsPaymentUserActionRequiredException will set
      // the transaction state to FAILED
      if (!$e instanceof jmsPaymentUserActionRequiredException)
        $this->state = self::STATE_FAILED;
      
      // check if there is any persistent data linked to this exception
      if ($e->hasPaymentMethodData())
        $this->updateFromPaymentMethodData($e->getPaymentMethodData());
      
      $this->save();
        
      // re-throw exception so it can be processed at the point in the stack
      // where it fits best (definitely not here)
      throw $e;
    }
  }
  
  /**
   * Abstract implementation which needs to be overwritten by sub classes.
   * This method also must take care of updating the payment record.
   * 
   * @throws jmsPaymentCommunicationException
   * @throws jmsPaymentFinancialException
   * @throws jmsPaymentFunctionNotSupportedException
   * @throws jmsPaymentTimeoutException
   * @throws jmsPaymentUserActionRequiredException
   * @throws jmsPaymentApprovalExpiredException
   * 
   * @return jmsPaymentMethodData
   */
  protected function doExecute()
  {
    throw new RuntimeException('This needs to be overwritten by sub classes.');
  }
  
  /**
   * Compiles the data object for this transaction
   * @return jmsPaymentMethodData
   */
  protected final function getPaymentMethodData()
  {
    $data = new jmsPaymentMethodData($this->requested_amount, $this->currency,
                 $this->Payment->DataContainer->toArray(false));
    
    return $data;
  }
  
  /**
   * Updates the persistent instances with the returned data
   * @param jmsPaymentMethodData $data
   */
  private function updateFromPaymentMethodData(jmsPaymentMethodData $data)
  {
    $this->response_code = $data->getResponseCode();
    $this->reason_code = $data->getReasonCode();
    $this->processed_amount = $data->getProcessedAmount();
    
    $dataContainer = $this->Payment->DataContainer;
    foreach ($data->getExtendedValues() as $name => $value)
      $dataContainer->$name = $value;
    $this->Payment->DataContainer->save();
  }
  
  /**
   * Returns the instance of the payment method for this transaction
   * @return jmsPaymentMethod
   */
  protected final function getPaymentMethodInstance()
  {
    $method = $this->Payment->DataContainer->method_class_name;
    
    if (!isset(self::$_paymentMethods[$method]))
    {
      self::$_paymentMethods[$method] = new $method();
    }
    
    return self::$_paymentMethods[$method];
  }
  
  /**
   * Make sure we return a float and not a string. This important for the
   * I18nNumber Widget/Validator to work properly.
   * 
   * @return float
   */
  public final function getRequestedAmount()
  {
    return floatval($this->_get('requested_amount'));
  }
  
  /**
   * Make sure we return a float and not a string. This important for the
   * I18nNumber Widget/Validator to work properly.
   * 
   * @return float
   */
  public final function getProcessedAmount()
  {
    return floatval($this->_get('processed_amount'));
  }
  
  /**
   * Whether this transaction is in a final state
   * @return boolean
   */  
  public final function isInFinalState()
  {
    return self::isFinalState($this->state);
  }
  
  /**
   * This cancels the transaction. Note that pending transactions cannot be
   * canceled since they have already modified the payment container.
   * 
   * @return void
   */
  public final function cancel()
  {
    $this->state = self::STATE_CANCELED;
    $this->save();
  }
}