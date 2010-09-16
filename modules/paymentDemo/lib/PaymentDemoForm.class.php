<?php
class PaymentDemoForm extends BaseForm
{
	private static $paymentMethods = array(
	  'MicropaymentDebit' => 'Micropayment Debit',
	  'Paypal' => 'PayPal',
	);
	
	public function configure()
	{
    $w = $this->widgetSchema;
    $v = new PaymentDemoValidatorSchema();
    $v->setPaymentMethods(array_keys(self::$paymentMethods));
    $this->setValidatorSchema($v);
    
    // amount
    $w['amount'] = new sfWidgetFormInput();
    $v['amount'] = new sfValidatorNumber(array(
      'min' => 0.01,
      'max' => 5000,
    ));
    
    // currency
    $w['currency'] = new sfWidgetFormI18nChoiceCurrency(array(
      'culture' => 'en',
      'currencies' => array('EUR', 'USD', 'GBP'),
    ));
    $v['currency'] = new sfValidatorChoice(array(
      'choices' => array('EUR', 'USD', 'GBP'),
      'multiple' => false,
    ));
    
    // method
    $w['method'] = new sfWidgetFormChoice(array(
      'choices' => self::$paymentMethods,
      'multiple' => false,
    ));
    $v['method'] = new sfValidatorChoice(array(
      'choices' => array_keys(self::$paymentMethods),
      'multiple' => false,
    ));
    
    // data for different payment methods
    foreach (array_keys(self::$paymentMethods) as $method)
    {
    	$formClass = $method.'PaymentDataForm';
    	$this->embedForm('method_'.$method, new $formClass());
    }

    $w->setNameFormat('paymentDemoForm[%s]');
	}
	
	public function getPaymentData()
	{
		if ($this->isValid() === false)
		  throw new LogicException('This method is only available on valid forms.');
		  
		$method = $this->getValue('method');
		$methodValues = $this->getValue('method_'.$method);
		
		$methodForm = $this->getEmbeddedForm('method_'.$method);
		
		return $methodForm->updateObject($methodValues);
	}
}