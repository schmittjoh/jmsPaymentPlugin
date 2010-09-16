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
 * 
 * @package jmsPaymentPlugin
 * @subpackage providers
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class jmsMicropaymentPaymentMethod extends jmsPaymentMethod
{
  /**
   * MSC dispatcher instance
   * @var TNvpServiceDispatcher
   */
  private $_dispatcher;  
  
  public function __construct()
  {
    $baseDir = sfConfig::get('sf_lib_dir').'/vendor/micropayment/';
    
    require_once $baseDir.'lib/init.php';
    require_once $baseDir.'lib/dispatcher/TNvpServiceDispatcher.php';
    require_once $baseDir.'services/'.$this->getServiceInterface().'.php';
  }
  
  /**
   * Returns an instance of the dispatcher. If it does not exist yet, it will
   * be instantiated.
   * 
   * @return TNvpServiceDispatcher
   */
  public final function getDispatcher()
  {
    if ($this->_dispatcher === null)
    {
      $this->_dispatcher = new TNvpServiceDispatcher(
        $this->getServiceInterface(), 
        $this->getNvpUrl()
      );
    }
    
    return $this->_dispatcher;
  }
  
  /**
   * This creates a customer, and returns the generated id.
   * 
   * @return string
   */
  protected function createCustomer()
  {
    try 
    {
      $result = $this->getDispatcher()->customerCreate(
        $this->getAccessKey(),
        $this->getTestMode()        
      );
      
      return $result['customerId'];
    }
    catch (Exception $e) 
    {
      throw jmsPaymentException::fromException($e);
    }
  }
  
  protected function getProjectCampaign(jmsPaymentMethodData $data)
  {
    if ($data['project_campaign'] !== null)
      return $data['project_campaign'];
      
    $config = sfConfig::get('app_jmsPaymentPlugin_micropayment');
    if (!isset($config['project_campaign']))
      throw new RuntimeException('You must either configure a "project_campaign"'
        .' in your app.yml, or set one for each payment individually.');
        
    return $config['project_campaign'];
  }
  
  protected function getProject(jmsPaymentMethodData $data)
  {
    if ($data['project'] !== null)
      return $data['project'];
      
    $config = sfConfig::get('app_jmsPaymentPlugin_micropayment');
    if (!isset($config['project']))
      throw new RuntimeException('You must either configure a "project" in your app.yml,'
        .' or set one for each payment individually.');
        
    return $config['project'];
  }
  
  /**
   * Returns the access key to use for the request
   * @return string
   */
  protected function getAccessKey()
  {
    $config = sfConfig::get('app_jmsPaymentPlugin_micropayment');
    
    if (!isset($config['access_key']))
      throw new RuntimeException('You must configure an "access_key" for micropayment.');
      
    return $config['access_key'];
  }
  
  /**
   * Returns whether to operate in test mode
   * @return integer
   */
  protected function getTestMode()
  {
    return $this->isDebug()? 1 : 0;
  }  
  
  /**
   * Returns the interface to use
   * @return string
   */
  abstract public function getServiceInterface();
  
  /**
   * Returns the service's NVP URL
   * @return string
   */
  abstract public function getNvpUrl();
}