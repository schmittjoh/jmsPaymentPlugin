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
 * Base payment exception
 * 
 * Do NOT extend jmsException to keep dependencies low.
 * 
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsPaymentException extends Exception
{
  /**
   * An array with updated payment data which is supposed to be persisted
   * even though the transaction method (approve, deposit, etc.) has not
   * completed normally.
   * 
   * @var jmsPaymentMethodData
   */
  private $_paymentMethodData;
  
  public final function setPaymentMethodData(jmsPaymentMethodData $data)
  {
    $this->_paymentMethodData = $data;
  }
  
  public final function hasPaymentMethodData()
  {
    return $this->_paymentMethodData !== null;
  }
  
  public final function getPaymentMethodData()
  {
    return $this->_paymentMethodData;
  }
}