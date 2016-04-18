<?php
/*
 * bool_helper.php
 *
 *  Created on: 2012-9-28
 *      Author: "Tao Guo<g@ur9.org>"
 */

if ( ! function_exists('check_str_bool')) {
	function check_str_bool($bool, $val)
	{
		return (strpos("{$bool}," , "{$val}," ) === false) ? false : true;
	}
}
