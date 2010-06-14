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
 * This interface can be implemented by tables whose records are listen to 
 * payment events.
 *  
 * You only need to implement this interface if you want to alter the 
 * loading process for event handlers. By default, only the handling record
 * itself is fetched from the database. But you also might want to fetch
 * certain relations which are mandatory for processing events. In this case,
 * you can implement this interface to avoid all relations from being lazy
 * loaded which can result in a huge number of queries.
 * 
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface jmsPaymentListenerTableInterface
{
  /**
   * Returns the object with the given id which is responsible
   * for handling the payment events.
   * 
   * @param array $id This is the primary key of the record, e.g.
   *                  array('id' => 5), or array('id' => 5, 'type' => 'blub')
   * @return mixed An instance of Doctrine_Record, or false
   */
  public function findPaymentEventHandler($id);
}