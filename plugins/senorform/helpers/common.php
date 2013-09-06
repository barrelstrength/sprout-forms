<?php 
require_once(dirname(__FILE__) . '/fields.php');
/**
 * Utility for debugging in templates; to use, call {% res = craft.senorform.dump(var) %}
 */
if( ! function_exists('dump'))
{
	function dump($msg)
	{
		echo '<pre>';var_dump($msg);echo '</pre>';
	}
}

