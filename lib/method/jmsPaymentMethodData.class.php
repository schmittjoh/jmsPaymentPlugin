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
 * This object is passed to the payment method when it is called from the 
 * financial transaction. We do not pass any Doctrine_Record objects in 
 * order to not depend on a specific ORM.
 * 
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsPaymentMethodData implements ArrayAccess
{
  /**
   * The amount that is requested to be processed by the payment method.
   * 
   * @var float
   */
  private $_amount;
  
  /**
   * The currency of the amount, and the processed amount.
   * TODO: Consider letting processed amount to have its own currency.
   * 
   * @var string
   */
  private $_currency;
  
  /**
   * These are usually the values from the PaymentData object.
   * 
   * @var array
   */
  private $_extendedValues;
  
  /**
   * Primary error code that is used to determine whether a transaction was 
   * successful or not.
   * 
   * @var string
   */
  private $_responseCode;
  
  /**
   * Secondary error code that is used to determine what went wrong exactly 
   * if anything.
   * 
   * @var string
   */
  private $_reasonCode;
  
  /**
   * The amount which was actually processed by the payment method. It does not
   * necessarily have to be equal to the requested amount.
   * 
   * @var float
   */
  private $_processedAmount;
  
  /**
   * @param float $amount
   * @param string $currency
   * @param array $values
   */
  public final function __construct($amount, $currency, array $values = array())
  {
    $this->_amount = floatval($amount);
    $this->_currency = $currency;
    $this->_extendedValues = $values;
  }
  
  /**
   * @param string $name
   * @param mixed $value
   */
  public final function setValue($name, $value)
  {
    $this->_extendedValues[$name] = $value;
  }
  
  /**
   * Whether a value with given name exists
   * @param string $name
   * @return boolean
   */
  public final function hasValue($name)
  {
    return array_key_exists($name, $this->_extendedValues);
  }
  
  /**
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public final function getValue($name, $default = null)
  {
    return isset($this->_extendedValues[$name])? 
      $this->_extendedValues[$name] : $default;
  }
  
  /**
   * Removes a value
   * @param string $name
   */
  public final function removeValue($name)
  {
    unset($this->_extendedValues[$name]);
  }
  
  /**
   * @return float
   */
  public final function getAmount()
  {
    return $this->_amount;
  }
  
  /**
   * @return string
   */
  public final function getCurrency()
  {
    return $this->_currency;
  }
  
  /**
   * @return array
   */
  public final function getExtendedValues()
  {
    return $this->_extendedValues;
  }
  
  /**
   * Sets the reason code.
   * See above for a definition of what the reason code is.
   * 
   * @param string $reasonCode
   */
  public final function setReasonCode($reasonCode)
  {
    $this->_reasonCode = $reasonCode;
  }
  
  /**
   * Returns the reason code
   * @return string
   */
  public final function getReasonCode()
  {
    return $this->_reasonCode;
  }
  
  /**
   * Sets the response code.
   * See above for a definition of what the response code is.
   * 
   * @param string $responseCode
   */
  public final function setResponseCode($responseCode)
  {
    $this->_responseCode = $responseCode;
  }
  
  /**
   * Returns the response code
   * @return string
   */
  public final function getResponseCode()
  {
    return $this->_responseCode;
  }
  
  /**
   * Sets the amount that was actually processed by the payment
   * @param float $amount
   */
  public final function setProcessedAmount($amount)
  {
    $this->_processedAmount = floatval($amount);
  }
  
  /**
   * Returns the processed amount
   * @return float
   */
  public final function getProcessedAmount()
  {
    return $this->_processedAmount;
  }
  
  /**
   * ArrayAccess interface
   * @param mixed $offset
   * @return mixed
   */
  public final function offsetGet($offset)
  {
    return $this->getValue($offset);
  }
  
  /**
   * ArrayAccess interface
   * @param mixed $offset
   * @param mixed $value
   */
  public final function offsetSet($offset, $value)
  {
    $this->setValue($offset, $value);
  }
  
  /**
   * ArrayAccess interface
   * @param mixed $offset
   */
  public final function offsetUnset($offset)
  {
    $this->setValue($offset, null);
  }
  
  /**
   * ArrayAccess interface
   * @param mixed $offset
   * @return boolean
   */
  public final function offsetExists($offset)
  {
    return $this->hasValue($offset);
  }
}