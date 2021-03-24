<?php
namespace Sale\Discount;

class ConditionDate extends ConditionAbstract {
		
    public static function getName() {
        return \Cetera\Application::getInstance()->getTranslator()->_('Дата');
    }
    
    public static function check($condition, $product, $offer, $is_in_cart) {

        $now = new DateTime('NOW');
        $value = new DateTime($condition['value']);
        
        if ($condition['condition'] == 'gt') {
            return $now >= $value;
        }	
        if ($condition['condition'] == 'lt') {
            return $now < $value;
        }
        if ($condition['condition'] == 'eq' || $condition['condition'] == 'like') {
            return $now == $value;
        }
        if ($condition['condition'] == 'neq' || $condition['condition'] == 'not_like') {
            return $now != $value;
        }
            
        return false;
    
    }
	
}