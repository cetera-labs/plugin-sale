<?php
namespace Sale\Discount;

abstract class ConditionAbstract  {
		
    abstract public static function getName();
    
    abstract public static function check($condition, $product, $offer, $is_in_cart);
	
}