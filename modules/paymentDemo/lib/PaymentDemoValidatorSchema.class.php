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


class PaymentDemoValidatorSchema extends sfValidatorSchema
{
  private $paymentMethods;
  
  public function preClean($values)
  {
    $method = isset($values['method']) && in_array($values['method'], $this->paymentMethods, true) 
               ? $values['method'] : reset($this->paymentMethods);
    
    // make sure to ignore fields of methods which were not selected
    $passValidator = new sfValidatorCallback(array(
      'callback' => array($this, 'validatorPass'),
    ));
    foreach ($this->paymentMethods as $pMethod)
    {
      if ($pMethod === $method)
        continue;
      
      $this->fields['method_'.$pMethod] = $passValidator;
    }
               
    parent::preClean($values);
  }
  
  /**
   * This custom pass validator makes sure that data objects of methods which
   * were not used, do not get saved in some cases.
   * 
   * @return null
   */
  public final function validatorPass()
  {
    return null;
  }
  
  public final function setPaymentMethods(array $methods)
  {
    $this->paymentMethods = $methods;
  }
  
  public final function getPaymentMethods()
  {
    return $this->paymentMethods;
  }
}