<?php
class mySimpleAmountForm extends BaseForm
{
	public function configure()
	{
		$w = $this->widgetSchema;
		$v = $this->validatorSchema;
		
		$w['amount'] = new sfWidgetFormInput();
		$v['amount'] = new sfValidatorNumber(array(
		  'min' => 0.01,
		  'max' => 5000,
		));
		
		$w['currency'] = new sfWidgetFormI18nChoiceCurrency(array(
		  'culture' => 'en',
		  'currencies' => array('EUR', 'USD', 'GBP'),
		));
		$v['currency'] = new sfValidatorChoice(array(
		  'choices' => array('EUR', 'USD', 'GBP'),
		  'multiple' => false,
		));
		
		$w->setNameFormat('simpleAmountForm[%s]');
	}
}