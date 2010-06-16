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
 * This interface provides some payment rules as to when which actions
 * is to be performed on the payment.
 * 
 * Possible Target States are:
 * DNE = Does Not Exist
 * APPROVED
 * DEPOSITED
 *  
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * TODO: This is currently not used, and must be refactored to the webshop plugin
 *       once that is implemented.
 */
interface jmsPaymentRuleInterface
{
  /**
   * DNE = does not exist
   * @var string
   */
  const COMMAND_DNE = 'dne';
  const COMMAND_APPROVED = 'approved';
  const COMMAND_DEPOSITED = 'deposited';
    
  /**
   * The order capture life cycle phase corresponds to the PrimePayment 
   * payment event.
   * 
   * @return string
   */
  public function getPrimeCommand();
  
  /**
   * The release to fulfillment life cycle phase corresponds to the 
   * ReservePayment payment event.
   * 
   * @return string
   */
  public function getReserveCommand();
  
  /**
   * The fulfillment (shipment confirmation) life cycle phase corresponds to a 
   * FinalizePayment payment event.
   * 
   * @return string
   */
  public function getFinalizeCommand();
}