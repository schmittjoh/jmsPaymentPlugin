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
 * Provides some utility functions for working with numbers
 * 
 * @package jmsPaymentPlugin
 * @subpackage util
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class jmsPaymentNumberUtil
{
  const EPSILON = 1.0E-8;
	
  /**
   * Compares two floating point number, and accommodates for precision
   * errors which can occur when floating numbers are converted to binary
   * values internally.
   * 
   * @see http://php.net/manual/en/language.types.float.php
   * @param float $float1
   * @param float $float2
   * @return integer Returns 1, -1 or 0 when $float1 > $float2, $float1 < $float2
   *                 or $float1 == $float2 respectively
   */
	public final static function compareFloats($float1, $float2)
	{
		if (abs($float1 - $float2) < self::EPSILON)
		  return 0;
		  
		return $float1 > $float2 ? 1 : -1;
	}
}