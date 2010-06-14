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

class jmsPaymentEvent
{
  private $_name;
  private $_target;
  private $_preventDefault = false;
  private $_stopPropagation = false;
  
  public function __construct($name, $target)
  {
    $this->_name = $name;
    $this->_target = $target;
  }

  public final function getName() 
  {
    return $this->_name;
  }
  
  public final function getTarget()
  {
    return $this->_target;
  }
  
  public final function isPreventDefault()
  {
    return $this->_preventDefault;
  }
  
  public final function preventDefault()
  {
    $this->_preventDefault = true;
  }
  
  public final function isStopPropagation()
  {
    return $this->_stopPropagation;
  }
  
  public final function stopPropagation()
  {
    $this->_stopPropagation = true;
  }
}