<?php
namespace Sale;

class Product extends Buyable
{
	const TABLE = 'sale_products';
	
	private static $discountData = null;
	private static $discountDataCart = null;

    public $correctDiscountValue = null;
	private $discount = null;
	private $activeDiscounts = null;
	
	private $offers = null;
	public $isInCart = false;
	
	public static function setCoupons($coupons)
	{
		self::$coupons = $coupons;
	}
	
	public function getActiveDiscounts() {
		if ($this->activeDiscounts === null) {
			$this->getDiscount();
		}
		return $this->activeDiscounts;
	}			
	
	public function getDiscount($field = 'price', $full_price = null, $is_in_cart = null, $offer_id = null) {
		if ($this->discount === null) {
			
			if ($full_price === null) {
				$full_price = $this->getFullPrice($field);
			}
			
			if ($is_in_cart === null) {
				$is_in_cart = $this->isInCart;
			}				
			
			if (!$is_in_cart || !count(Cart::get()->getCoupons())) {
				$dd = 'discountData';
				$w = 'B.id IS NULL';
			}
			else {
				$dd = 'discountDataCart';
				$params = [];
				foreach (Cart::get()->getCoupons() as $c) {
					$params[] = $c['id'];
				}
                $w = 'B.id IS NULL or (B.active=1 and B.id IN ('.implode(',',$params).'))';				
			}
		
			if (self::$$dd === null) {
								
				self::$$dd = self::getDbConnection()->fetchAll('
					SELECT A.* 
					FROM sale_discount A 
					LEFT JOIN sale_coupon B ON (A.id=B.discount_id) 
					WHERE A.active=1 and '.$w.'
					GROUP BY A.id					
					ORDER BY A.priority
				');
							
			}
				
			$this->discount = 0;
			$this->activeDiscounts = [];
			
			foreach (self::$$dd as $discount) {
							
				$discount['conditions'] = json_decode($discount['conditions'], true);
							
				if (!$this->checkCondition($discount['conditions'], $is_in_cart, $offer_id)) continue;

				switch ($discount['value_type']) {
					case 0:
						$value = $discount['value'] * $full_price/100;
						break;
					case 1:
						$value = $discount['value'];
						break;
					case 2:
						$value = $full_price - $discount['value_type'];
						break;
					default: 
						$value = 0;
				}
				
				if ($this->correctDiscountValue) {
					$value = call_user_func($this->correctDiscountValue['function'], $value, $this->correctDiscountValue['params']);
					$this->correctDiscountValue = null;
				}
				
				if ($discount['max_discount'] && $value > $discount['max_discount']) $value = $discount['max_discount'];
				
				$this->discount = $this->discount + $value;
				$this->activeDiscounts[] = $discount;
				
				if ($discount['last_discount']) break;			
			}
			$this->discount = round($this->discount, $this->getPriceDecimals());
			
		}
		return $this->discount;
	}
		
	private function checkCondition($conditions, $is_in_cart, $offer_id)
	{
		$fields = $this->getFieldsDef();
	
		foreach ($conditions['conditions'] as $condition) {
			
			$match = false;
						
			try {	
			
                if (\Sale\Discount::isConditionExists($condition['field'])) {
                    $match = $condition['field']::check($condition, $this, $offer_id, $is_in_cart);
                }
				// скидка по количеству товаров в корзине УДАЛИТЬ!!!
				elseif ($condition['field'] == 'cart_quantity') {
					
					// товаров в корзине больше чем указано, то делаем скидку на эти товары
					if ($condition['condition'] == 'gt') {
						$match = Cart::get()->getProductsCount() >= $condition['value'];
					}	
					// скидка только на указанное кол-во самых дешевых товаров
					if ($condition['condition'] == 'lt') {
						$match = true;
						
						$this->correctDiscountValue = [
							'function' => function($value, $params) {
								
											  // товары в корзине без учета скидок
											  $products = Cart::get()->getProducts(false);								  											  
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
								'pid'               => $this->id,
								'oid'               => $offer_id,
								'max_cart_quantity' => $condition['value'],
							]
						
						];
						
					}
				}				
				else {
					
					if (!isset($fields[$condition['field']])) throw new \Exception('Field "'.$condition['field'].'" does not exist');
				
					$field = $fields[$condition['field']];
					$field_value = $this->getDynamicField($condition['field']);					
					
					// скидка по принадлежности к разделу
					if ($condition['field'] == 'idcat') {
						if ($condition['condition'] == 'eq') {
							$match = $field_value == $condition['value'];
						}	
						elseif ($condition['condition'] == 'neq') {
							$match = $field_value != $condition['value'];
						}
						elseif ($condition['condition'] == 'like') {
							$match = $this->catalog->getPath()->has($condition['value']);
						}
						elseif ($condition['condition'] == 'not_like') {
							$match = !$this->catalog->getPath()->has($condition['value']);
						}	
					}
					// скидка по полю товара
					else {
						switch ($field['type']) {
							
							case FIELD_LINK:
							case FIELD_MATERIAL:
							case FIELD_FORM:
								if ($condition['condition'] == 'eq') {
									$match = $field_value->id == $condition['value'];
								}
								elseif ($condition['condition'] == 'neq') {
									$match = $field_value->id != $condition['value'];
								}
								break;
								
							case FIELD_INTEGER:
							case FIELD_DOUBLE:
							case FIELD_BOOLEAN:
								if ($condition['condition'] == 'eq') {
									$match = $field_value == $condition['value'];
								}	
								elseif ($condition['condition'] == 'neq') {
									$match = $field_value != $condition['value'];
								}
								elseif ($condition['condition'] == 'gt') {
									$match = $field_value >= $condition['value'];
								}
								elseif ($condition['condition'] == 'lt') {
									$match = $field_value < $condition['value'];
								}
								break;
								
							case FIELD_TEXT:
							case FIELD_LONGTEXT:
							case FIELD_FILE:
							case FIELD_DATETIME:
							case FIELD_ENUM:
							case FIELD_HUGETEXT:
								if ($condition['condition'] == 'eq') {
									$match = $field_value == $condition['value'];
								}	
								elseif ($condition['condition'] == 'neq') {
									$match = $field_value != $condition['value'];
								}
								elseif ($condition['condition'] == 'like') {
									$match = mb_substr_count ( mb_strtolower($field_value) , mb_strtolower($condition['value']) ) > 0;
								}
								elseif ($condition['condition'] == 'not_like') {
									$match = mb_substr_count ( mb_strtolower($field_value) , mb_strtolower($condition['value']) ) == 0;
								}					
								break;						
							
						}
					}
				
				}
			}
			catch (\Exception $e) {
				if (\Cetera\Application::getInstance()->isDebugMode()) throw $e;
			}
			
			if ($match &&  $conditions['logic'] == 'or' && $conditions['logic2'] == 0 ) return true;
			if (!$match && $conditions['logic'] == 'or' && $conditions['logic2'] == 1 ) return true;
			if (!$match && $conditions['logic'] == 'and' && $conditions['logic2'] == 0 ) return false;
			if ($match &&  $conditions['logic'] == 'and' && $conditions['logic2'] == 1 ) return false;
		}
		if ($conditions['logic'] == 'and') return true;
		if ($conditions['logic'] == 'or') return false;
		return true;
	}
		
	public function hasOffers()
	{
		return $this->getOffers()->getCountAll() > 0;
	}		

	public function getOffers()
	{
		if ($this->offers === null) {
			$this->offers = Offer::getObjectDefinition()->getMaterials()->where('product='.(int)$this->id);
		}
        
		return $this->offers;
	}
	
	public function isInWishList() {
		return WishList::get()->checkProduct( $this );
	}
	
}