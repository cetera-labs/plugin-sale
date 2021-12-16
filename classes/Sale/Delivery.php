<?php
namespace Sale;

class Delivery  {
	
	use \Cetera\DbConnection;
	
	private static $calculators = array();
	private $order;
	private $data = [
        'calculator' => '\\Sale\\DeliveryCalculator\\Unknown',
        'calculator_params' => '',
    ];
	public $id;
	public $name;
	private $cost = null;
	private $calculator = null;
	
	public static function addCalculator($class)
	{
		if (is_subclass_of($class, '\Sale\DeliveryCalculator\CalculatorAbstract'))
		{
			self::$calculators[] = $class;
		} 
		else 
		{
		    throw new \LogicException("{$class} must extend \\Sale\\DeliveryCalculator\\CalculatorAbstract");
		}		
	}
	
	public static function getCalculators()
	{
		$data = array();
		foreach (self::$calculators as $c)
		{
			$d = $c::getInfo();
			$d['class'] = $c;
			$d['id'] = $c;
			$data[] = $d;
		}
		return $data;
	}
	
	public function __construct($data, Order $order)
	{
		$this->order = $order;
        if (is_array($data)) {
            $this->data = $data;
            $this->id = $data['id'];
            $this->name = $data['name'];
        }
	}
	
	public function getCalculator()
	{
		if ($this->calculator == null)
		{
            $class = $this->data['calculator'];
			if (!in_array($class, self::$calculators)) {
				$class = '\\Sale\\DeliveryCalculator\\Unknown';
			}
			$this->calculator = new $class(json_decode($this->data['calculator_params'], true), $this->order);
		}
		return $this->calculator;
	}
	
	public function getHtml()
	{
		return $this->getCalculator()->getHtml();
	}
	
	public function getNote()
	{
		return $this->getCalculator()->getNote();
	}	
		
	public function getCost($display = false, $exceptions = true)
	{
		if ($this->cost === null)
		{
			try
			{
				$calc = $this->getCalculator();
				$this->cost = $this->order->getCurrency()->convert($calc->calculate(), $calc->getCurrency());
			}
			catch (\Exception $e)
			{
				if ($exceptions) throw $e;
				return -1;
			}
		}
		if ($display) return $this->order->getCurrency()->format( $this->cost );
		return $this->cost;		
	}	
}