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

class jmsPaymentTransactionEvent extends jmsPaymentEvent
{
  private $_transaction;
  
  public function __construct($name, Payment $payment, FinancialTransaction $transaction)
  {
    parent::__construct($name, $payment);
    
    $this->_transaction = $transaction;
  }
  
  public final function getTransaction()
  {
    return $this->_transaction;
  }
}