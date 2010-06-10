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
 * This is thrown as part of the jmsPaymentUserActionRequiredException 
 * when the user needs to visit the given URL to complete a financial
 * transaction.
 * 
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsPaymentUserActionVisitURL extends jmsPaymentUserAction
{
	const TYPE = 'visit_url';
	
	/**
	 * A URL that the user needs to visit to complete the transaction
	 * @var string
	 */
	private $_url;
	
	/**
	 * @param string $url
	 */
	public function __construct($url)
	{
		parent::__construct(self::TYPE);
		
		$this->_url = $url;
	}
	
	/**
	 * @return string
	 */
	public final function getURL()
	{
		return $this->_url;
	}
}