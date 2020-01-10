<?php
namespace Sale;

class Payment  {
		
	private static $gateways = array();
	
	public static function addGateway($class)
	{
		if (is_subclass_of($class, '\Sale\PaymentGateway\GatewayAbstract'))
		{
			self::$gateways[] = $class;
		} 
		else 
		{
		    throw new \LogicException("{$class} must extend \\Sale\\PaymentGateway\\GatewayAbstract");
		}		
	}
	
	public static function isGatewayExists($class)
	{
		return in_array($class, self::$gateways);
	}
	
	public static function getGateways()
	{
		$data = array(array(
			'id'    => 'empty',
			'class' => '',
			'name'  => '-',
		));
		foreach (self::$gateways as $g) {
			$d = $g::getInfo();
			$d['class'] = $g;
			$d['id'] = $g;
			$data[] = $d;
		}
		return $data;
	}
	
}