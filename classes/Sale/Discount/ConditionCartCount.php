<?php
namespace Sale\Discount;

class ConditionCartCount extends ConditionAbstract {
		
    public static function getName() {
        $a = \Cetera\Application::getInstance();
        return $a->getTranslator()->_('Кол-во товаров в корзине');
    }
    
    public static function check($condition, $product, $offer, $is_in_cart) {

        $match = false;
        
        // товаров в корзине больше чем указано, то делаем скидку на эти товары
        if ($condition['condition'] == 'gt') {
            $match = \Sale\Cart::get()->getProductsCount() >= $condition['value'];
        }	
        // товаров в корзине <= чем указано, то делаем скидку на эти товары
        if ($condition['condition'] == 'le') {
            $match = \Sale\Cart::get()->getProductsCount() <= $condition['value'];
        }	
        // товаров в корзине равное количество, то делаем скидку на эти товары
        if ($condition['condition'] == 'eq') {
            $match = \Sale\Cart::get()->getProductsCount() == $condition['value'];
        }	
        // скидка только на указанное кол-во самых дешевых товаров
        if ($condition['condition'] == 'lt') {
            $match = true;

            $product->correctDiscountValue = [
                'function' => function($value, $params) {
                    
                                  // товары в корзине без учета скидок
                                  $products = \Sale\Cart::get()->getProducts(false);								  											  
                                  usort ( $products , function($a,$b){ if ($a['price'] == $b['price']) return 0;  return ($a['price'] < $b['price']) ? -1 : 1; } );
                                  $count = 0;
                                  foreach ($products as $p) {												  
                                      
                                      if ( $p['product'] && $p['product']->id == $params['pid'] && (!$p['offer'] || $p['offer']->id == $params['oid']) ) {
                                          // товар попал в указанное кол-во самых дешевых
                                          
                                          $q = $params['max_cart_quantity'] - $count - 1; // макс кол-во ДАННОГО товара со скидкой
                                          if ($q >= $p['quantity']) return $value; // уместились в это количество
                                          
                                          // пересчет скидки
                                          $value = $q * $value / $p['quantity'];		
                                      
                                          return $value;
                                      }
                                      
                                      $count += $p['quantity'];
                                      if ($count >= $params['max_cart_quantity']) {
                                          // товар не попал в указанное кол-во самых дешевых: скидка=0
                                          return 0;
                                      }
                                  }
                                  return 0;
                              },
                'params' => [
                    'pid'               => $product->id,
                    'oid'               => $offer->id,
                    'max_cart_quantity' => $condition['value'],
                ]
            
            ];
            
        }    
        
        return $match;
    
    }
	
}