<?php
namespace Sale;

class Compare {
	
	use \Cetera\Traits\Extendable;
	
	protected $s;
	protected $limit;
	protected static $current = null;
	protected $products = null;
	
	public static function get( $limit = 0 )
	{
        if (null === static::$current) {
			static::$current = static::create(); 
			static::$current->limit = $limit;
        }
		return static::$current;
	}	
	
	protected function __construct()
	{
		$this->s = \Cetera\Application::getInstance()->getSession();
	}
		
	/*
	* добавить продукт в список
	*/
	public function addProduct( $product )
	{	
		if ( $product instanceOf Product ) {
			$pid = $product->id;
		}
		else {
			$pid = $product;
		}
		
		if (!$pid) throw new \Exception(  $this->t->_('Не указан продукт') );
		
		if ($this->findIndex( $product ) < 0) {
		
			if (!isset($this->s->saleCompareList)) {
				$this->s->saleCompareList = [];
			}
			$this->s->saleCompareList[] = $pid;
			
			if ($this->limit) {
				while (count($this->s->saleCompareList) > $this->limit) {
					array_unshift($this->s->saleCompareList);
				}
			}
			
			$this->products = null;
		
		}
		
		return $this;
	}	
	
	private function findIndex( $product )
	{
		if ( $product instanceOf Product ) {
			$pid = $product->id;
		}
		else  {
			$pid = $product;
		}
		
		if (isset($this->s->saleCompareList)) {
			foreach($this->s->saleCompareList as $id => $p) {
				if ($pid == $p) return $id;
			}
		}
		return -1;		
	}
	
	public function removeProduct( $product )
	{
		$idx = $this->findIndex( $product );
		
		if ($idx >= 0) {
			array_splice($this->s->saleCompareList, $idx, 1);
			$this->products = null;
		}
		
		return $this;		
	}
	
	/*
	* получить продукты в списке
	*/	
	public function getProducts()
	{
		if (!$this->products) {
			$this->products = [];
			if (isset($this->s->saleCompareList) && is_array($this->s->saleCompareList)) {
				foreach ($this->s->saleCompareList as $pid) {
					
					try {
						$this->products[] = Product::getById( $pid );					
						if ($this->limit && count($this->products) == $this->limit) {
							break;
						}
					}
					catch (\Exception $e) {}
					
				}
			}	
		}
		return $this->products;
	}
	
	public function getProductsCount()
	{
		if (!isset($this->s->saleCompareList)) {
			return 0;
		}
		else {
			$c = count($this->s->saleCompareList);
			if ($this->limit) {
				$c = min($c, $this->limit);
			}
			return $c;
		}
	}
	
	public function clear()
	{
		unset($this->s->saleCompareList);
		$this->products = null;
		return $this;
	}	
	
	public function isValuesDiffer($field)
	{
		$v = '** undefined **';
		foreach ( $this->getProducts() as $p ) {
			if ($v === '** undefined **') {
				$v = $this->fieldValue($p, $field);
				continue;
			}
			if ($v != $this->fieldValue($p, $field) ) {
				return true;
			}
		}
		return false;
	}
	
	public function fieldValue($product, $field)
	{
		$value = $product->{$field->name};
		
		if ($field instanceof \Cetera\ObjectFieldLink) {
			return $value->name;	
		}
		else {		
			return $value;
		}
	}		
	
}