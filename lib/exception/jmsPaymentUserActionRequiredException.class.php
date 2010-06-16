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
 * This is thrown if interaction from the user is required to complete
 * a financial transaction.
 * 
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsPaymentUserActionRequiredException extends jmsPaymentException
{
  private $_action;
  
  public function __construct(jmsPaymentUserAction $action, $message = '', 
                              $code = 0, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
    $this->_action = $action;
  }
  
  public final function getAction()
  {
    return $this->_action;
  }
}