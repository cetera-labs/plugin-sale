<?php
namespace Sale;

abstract class Buyable extends \Cetera\Material
{
	protected static $priceDecimals = null;
    
    public $options = null;
	
	public static function fetch($data, $type = 0, $table = NULL)
	{
		return parent::fetch($data,  self::getObjectDefinition() );
	}
	
	public static function getById($id, $type = 0, $table = NULL)
    {
		return parent::getById($id,  self::getObjectDefinition() );
    }	
	
	public static function getObjectDefinition()
	{
		return \Cetera\ObjectDefinition::findByAlias(static::TABLE);
	}
    
	public static function enum()
	{
		return static::getObjectDefinition()->getMaterials();
	}    
	
	abstract public function getDiscount($field = 'price');
	
	public function getDiscountPercent($field = 'price') {
		return $this->getDiscount($field) * $this->getFullPrice($field)/100;
	}
	
	public function getCurrency()
	{
		$c = $this->getDynamicField('currency');
		if (!$c) $c = -1;
		return Currency::getByCode( $c );
	}	
	
	public function getPriceDecimals()	
	{
		if (self::$priceDecimals === null) {
			self::$priceDecimals = \Sale\Setup::configGet( 'price_decimals' );
			if (self::$priceDecimals === null) {
				self::$priceDecimals = 2;
			}
		}
		return self::$priceDecimals;
	}

	public function canBuy($quantity = 1)
	{
		if ( !\Sale\Setup::configGet( 'use_quantity' ) ) return true;
		if ($this->quantity >= $quantity) return true;
		return false;
	}	

	public function getDisplayPrice($field = 'price')
	{
		return $this->getCurrency()->format($this->getPrice($field));
	}
	
	public function getDisplayDiscountPrice($field = 'price')
	{
		return $this->getCurrency()->format($this->getDiscountPrice($field));
	}

	public function getDisplayFullPrice($field = 'price')
	{
		return $this->getCurrency()->format($this->getFullPrice($field));
	}	
	
	public function getDisplayDiscount($field = 'price')
	{
		return $this->getCurrency()->format($this->getDiscount($field));
	}
	
	public function getPrice($field = 'price')
	{
		return $this->getDiscountPrice($field);
	}

	public function getFullPrice($field = 'price')
	{
		return round($this->getDynamicField($field), $this->getPriceDecimals());
	}	
	
	public function getDiscountPrice($field = 'price')
	{
		$value = $this->getFullPrice($field) - $this->getDiscount($field);
		if ($value < 0) $value = 0;
		return (float)$value;
	}	
	
}