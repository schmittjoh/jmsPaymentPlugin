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
 * This task updates our local database with the current exchanges rates.
 * All rates are using the EUR as a base.
 * 
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsPaymentPluginUpdateRatesTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'jmsPaymentPlugin';
    $this->name             = 'updateRates';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [jmsPaymentPlugin:updateRates|INFO] task does things.
Call it with:

  [php symfony jmsPaymentPlugin:updateRates|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    // update rates
    list($rates) = $this->getRates();
    foreach ($rates as $rateInfo)
    {
      list($code, $rate) = $rateInfo;
      $currency = Doctrine_Core::getTable('Currency')->findOneByCode($code);
      if (!$currency)
      { 
        $currency = new Currency();
        $currency->code = $code;
      }
      
      $currency->rate = $rate;
      $currency->save();
    }
    
    $this->logSection('payment', 'Exchange rates have been updated.');
  }
  
  protected function getRates()
  {
    //Read eurofxref-daily.xml file in memory 
    $XMLContent= file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
    //the file is updated daily between 2.15 p.m. and 3.00 p.m. CET
    $exchangeRates = array();
    $date = "";
    $found = false;
    foreach ($XMLContent as $line) 
    {
      if(!$found && ereg("time='([[:graph:]]+)'",$line,$validityDate))
      {
        $date = $validityDate[1];
        $found = true;
      }
      if (ereg("currency='([[:alpha:]]+)'",$line,$currencyCode)) 
      {
        if (ereg("rate='([[:graph:]]+)'",$line,$rate)) 
        {
          array_push($exchangeRates, array($currencyCode[1], $rate[1]));
        }
      }
    }
    
    // EUR is not in the exchange rates, but still is a currency; so, we need
    // to add it manually
    $exchangeRates[] = array('EUR', 1.0);
    
    return array($exchangeRates, $date);
  }
}
