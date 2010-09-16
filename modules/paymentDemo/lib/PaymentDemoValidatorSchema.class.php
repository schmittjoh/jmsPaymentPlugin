<?php
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