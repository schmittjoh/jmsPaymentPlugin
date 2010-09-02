<?php
abstract class jmsMicropaymentPaymentMethod extends jmsPaymentMethod
{
  /**
   * MSC dispatcher instance
   * @var TNvpServiceDispatcher
   */
  private $_dispatcher;	
  
  public function __construct()
  {
  	require_once sfConfig::get('sf_lib_dir').'/vendor/micropayment/lib/init.php';
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