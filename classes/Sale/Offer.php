<?php
namespace Sale;

/*
* Торговое предложение
*/
class Offer extends Buyable
{
	
	const TABLE = 'sale_offers';
	
	public $isInCart = false;
	
	public function getDiscount($field = 'price')
	{
		if (!$this->product) {
			$value = 0;
		}
		else {
			$value = $this->product->getDiscount($field, $this->getFullPrice($field), $this->isInCart, $this);
		}
		return $value;
	}
	
}