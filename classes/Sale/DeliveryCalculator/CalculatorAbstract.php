<?php
namespace Sale\DeliveryCalculator;

abstract class CalculatorAbstract  {
	
	public $params;
	public $order;
	protected $t = NULL;
	
	public function __construct($params, $order)
	{
		$this->params = $params;
		$this->order = $order;	
		$this->t = \Cetera\Application::getInstance()->getTranslator();
	}
	
	public static function getInfo()
	{
		return [];
	}
	
	/* расчет стоимости доставки */
	abstract public function calculate();
	
	public function getCurrency()
	{
		return \Sale\Currency::getDefault();
	}	
	
	public function getHtml()
	{
		return '';
	}
	
	public function getData()
	{
		return null;
	}	
	
	public function getNote()
	{
		return null;
	}		
	
}