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

class jmsPaymentMethod implements jmsPaymentMethodInterface
{
  /**
   * @see plugins/jmsPaymentPlugin/lib/interface/jmsPaymentMethodInterface#approve($data, $retry)
   */
  public function approve(jmsPaymentMethodData $data, $retry = false)
  {
    throw new jmsPaymentFunctionNotSupportedException;
  }
  
  /**
   * At least, this method must be overwritten. The others are optional.
   * @see plugins/jmsPaymentPlugin/lib/interface/jmsPaymentMethodInterface#deposit($data, $retry)
   */
  public function deposit(jmsPaymentMethodData $data, $retry = false)
  {
    throw new jmsPaymentFunctionNotSupportedException;
  }
  
  /**
   * @see plugins/jmsPaymentPlugin/lib/interface/jmsPaymentMethodInterface#reverseApproval($data, $retry)
   */
  public function reverseApproval(jmsPaymentMethodData $data, $retry = false)
  {
    throw new jmsPaymentFunctionNotSupportedException;
  }
  
  /**
   * @see plugins/jmsPaymentPlugin/lib/interface/jmsPaymentMethodInterface#reverseDeposit($data, $retry)
   */
  public function reverseDeposit(jmsPaymentMethodData $data, $retry = false)
  {
    throw new jmsPaymentFunctionNotSupportedException;
  }

  /**
   * @see plugins/jmsPaymentPlugin/lib/interface/jmsPaymentMethodInterface#isDebug()
   */
  protected function isDebug()
  {
    return sfConfig::get('sf_debug', false);
  }
}