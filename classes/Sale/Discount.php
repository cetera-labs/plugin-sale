<?php
namespace Sale;

class Discount  {
		
	private static $conditions = array();
	
	public static function addCondition($class)
	{
		if (is_subclass_of($class, '\\Sale\\Discount\\ConditionAbstract')) {
			self::$conditions[] = $class;
		} 
		else {
		    throw new \LogicException("{$class} must extend \\Sale\\Discount\\ConditionAbstract");
		}	
	}
	
	public static function isConditionExists($class)
	{
		return in_array($class, self::$conditions);
	}
	
	public static function getConditions()
	{
        $data = [];
		foreach (self::$conditions as $g) {
			$d = [
                'id' => $g,
                'alias' => $g,
                'name'  => $g::getName()
            ];
			$data[] = $d;
		}
		return $data;
	}
	
}