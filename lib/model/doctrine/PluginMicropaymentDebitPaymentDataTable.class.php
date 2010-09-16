<?php

/**
 * PluginMicropaymentDebitPaymentDataTable
 * 
 * @package jmsPaymentPlugin
 * @subpackage model
 * @author Johannes M. Schmitt <schmittjoh@gmail.com> 
 */
class PluginMicropaymentDebitPaymentDataTable extends PaymentDataTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object PluginMicropaymentDebitPaymentDataTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('PluginMicropaymentDebitPaymentData');
    }
}