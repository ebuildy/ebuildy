<?php

namespace eBuildy\Helper;

class ArrayHelper
{
	static public function safeUnset(&$source, $arg1)
	{
		$args = func_get_args();
		
		array_shift($args);

		foreach($args as $arg)
		{
			if (isset($source[$arg]))
			{
				unset($source[$arg]);
			}
		}
	}
}
