<?php

/**
 * PluginMicropaymentDebitPaymentData form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginMicropaymentDebitPaymentDataForm extends BaseMicropaymentDebitPaymentDataForm
{
	public function configure()
	{
		parent::configure();
		
		$w = $this->widgetSchema;
		$v = $this->validatorSchema;
		
		// account holder
		$v['account_holder']->setOption('required', true);
		$v['account_holder']->setOption('trim', true);
		
		// account number
		$v['account_number']->setOption('required', true);
		$v['account_number']->setOption('trim', true);
		
		// bank code
		$v['bank_code']->setOption('required', true);
		$v['bank_code']->setOption('trim', true);
		
		// bank country
		$w['bank_country'] = new sfWidgetFormI18nChoiceCountry(array(
		  'culture' => 'en',
		));
		$v['bank_country'] = new sfValidatorChoice(array(
		  'choices' => array_keys($w['bank_country']->getOption('choices')),
		));
		$v->setOption('required', true);
		
		$this->useFields(array(
		  'account_holder',
		  'account_number',
      'bank_country',
      'bank_code',
		), true);
	}
}
