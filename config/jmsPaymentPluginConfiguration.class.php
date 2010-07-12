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
 * jmsPaymentPlugin configuration.
 * 
 * @package     prometheus
 * @subpackage  payment
 * @author      Johannes M. Schmitt <schmittjoh@gmail.com>
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class jmsPaymentPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.2-DEV';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('command.post_command', 
      array('jmsPaymentPluginConfiguration', 'listenToCommandPostCommand'));
  }
  
  public static function listenToCommandPostCommand(sfEvent $event)
  {
    static $firstCall = array();
    $task = $event->getSubject();
    $taskClass = get_class($task);
    $isFirstCall = isset($firstCall[$taskClass]) === false;
    $firstCall[$taskClass] = true;
    
    switch ($taskClass) 
    {
      case 'sfDoctrineDataLoadTask':
        if ($isFirstCall)
        {
          passthru(sprintf('%s %s/symfony jmsPaymentPlugin:updateRates',
            sfToolkit::getPhpCli(), sfConfig::get('sf_root_dir')));
        }
        break;
    }
  } 
}
