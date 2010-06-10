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

interface jmsPaymentMethodInterface
{
	/**
   * Verifies that the customer is allowed to make the purchase. The approve 
   * action helps to ensure that a customer has adequate funds available to make 
   * the purchase. Depending on the payment type and business policy, varying 
   * actions will be performed. For example, in the case of credit cards, a credit
   * card authorization request is sent and a transaction is approved, thereby 
   * ensuring the merchant will receive payment. A positive approval results in an 
   * authorization code being generated, and those funds being set aside. The 
   * cardholder's credit limit is then reduced by the authorized or approved amount.
   * The intention is that payment problems that are detected can be communicated 
   * back to the customer while the customer is online. The approve action does not 
   * apply to all payment methods. For instance, it would not make sense for an 
   * approve action to occur for electronic check (ACH) transactions. 
   *  
	 * @param jmsPaymentMethodData $data
	 * @param boolean $retry
   * @throws jmsPaymentUserActionRequiredException if an action of the user is required
   * @throws jmsPaymentCommunicationException if there occurred an error during the operation.
   * @throws jmsPaymentFunctionNotSupportedException if this operation is not supported by this method.
   * @throws jmsPaymentTimeoutException if the operation experienced a time out during its operation.
   * @return void
	 */
	public function approve(jmsPaymentMethodData $data, $retry = false);
	
  /**
   * Captures a payment for an order. In general, communication with a payment 
   * back-end system or payment processor occurs at this stage.
   *  
   * @param jmsPaymentMethodData $data
   * @param boolean $retry
   * @throws jmsPaymentUserActionRequiredException if an action of the user is required
   * @throws jmsPaymentApprovalExpiredException if the payment approval (authorization) has expired.
   * @throws jmsPaymentCommunicationException if there occurred an error during the operation.
   * @throws jmsPaymentFunctionNotSupportedException if this operation is not supported by this method.
   * @throws jmsPaymentTimeoutException if the operation experienced a time out during its operation.
   * @return void
   */
	public function deposit(jmsPaymentMethodData $data, $retry = false);
	
  /**
   * Voids an approval. Only full reversals are supported; reversal of partial 
   * amounts is not supported. 
   * 
   * @param jmsPaymentMethodData $data
   * @param boolean $retry
   * @throws jmsPaymentUserActionRequiredException if an action of the user is required
   * @throws jmsPaymentCommunicationException if there occurred an error during the operation.
   * @throws jmsPaymentFunctionNotSupportedException if this operation is not supported by this method.
   * @throws jmsPaymentTimeoutException if the operation experienced a time out during its operation.
   * @return void
   */
	public function reverseApproval(jmsPaymentMethodData $data, $retry = false);
	
  /**
   * Voids a deposit. Only full reversals are supported; reversal of partial
   * amounts is not supported.
   * 
   * @param jmsPaymentMethodData $data
   * @param boolean $retry
   * @throws jmsPaymentUserActionRequiredException if an action of the user is required
   * @throws jmsPaymentCommunicationException if there occurred an error during the operation.
   * @throws jmsPaymentFunctionNotSupportedException if this operation is not supported by this method.
   * @throws jmsPaymentTimeoutException if the operation experienced a time out during its operation.
   * @return void
   */
	public function reverseDeposit(jmsPaymentMethodData $data, $retry = false);
}