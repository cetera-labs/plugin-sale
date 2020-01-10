<?php
namespace Sale;

class Cart extends \Cetera\Base {
	
	use \Cetera\DbConnection, \Cetera\Traits\Extendable;
	
	protected $id = 0;
	protected static $current = null;
	
	protected $_totalSum = null;
	protected $_totalDiscountSum = null;
	protected $coupons = [];
	
	protected static function getById($id)
	{
		$cart = static::create();
		$cart->id = $id;		
		$d = self::getDbConnection()->fetchAll('SELECT B.* FROM sale_cart_coupons A INNER JOIN sale_coupon B ON (A.coupon_id=B.id and B.active=1) WHERE A.cart_id = ?', array( $cart->id ));
		foreach ($d as $c) {
			$cart->coupons[$c['id']] = $c;
		}
		return $cart;			
	}
	
	/*
	* возвращает корзину текущего пользователя
	*/	
	public static function get()
	{
        if (null === static::$current)
		{
			$a = \Cetera\Application::getInstance();
			$user = $a->getUser();
			$data = null;
			$data2 = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_carts WHERE user_id = 0 and uid = ?', array( $a->getUid() ));
			if ($user) {
				$data = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_carts WHERE user_id = ?', array($user->id));
				if ($data) {
					if ($data2) {
						$old = static::getById($data['id']);					
						$new = static::getById($data2['id']);
						if ($new->getProductsCount() > 0 ) {
							$old->delete();
							$data = $data2;
							self::getDbConnection()->update('sale_carts', array(
								'user_id' => $user->id
							), array(
								'id' => $data['id']
							));	
						}
						else {
							$new->delete();
						}
					}
				}
				else {
					$data = $data2;
				}
			}
			else {
				$data = $data2;
			}
			
			if (!$data) {
                $cid = 0;
			}
			else {
				$cid = $data['id'];
			}
			static::$current = static::getById($cid);
        }
		return static::$current;
	}
	
	public function getId()
	{
		return $this->id;
	}
    
    private function checkId()
    {
        if ($this->id > 0) return;
        $a = \Cetera\Application::getInstance();
        $user = $a->getUser();
        self::getDbConnection()->insert('sale_carts', array(
            'date'    => new \DateTime(),
            'user_id' => $user?$user->id:0,
            'uid'     => $a->getUid(),
        ),array('datetime') );
        $this->id = self::getDbConnection()->lastInsertId();
    }
	
	public function getCoupons()
	{
		return $this->coupons;
	}	

	public function addCoupon($coupon)
	{
		$t = \Cetera\Application::getInstance()->getTranslator();
		$d = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_coupon WHERE active=1 and code=?', array( $coupon ));
		if (!$d) throw new \Exception($t->_('Купон "'.$coupon.'" не найден или уже был использован'));
		if (!isset($this->coupons[$d['id']])) {
            $this->checkId();
			$this->coupons[$d['id']] = $d;
			self::getDbConnection()->insert('sale_cart_coupons', array(
				'coupon_id'  => $d['id'],
				'cart_id'  => $this->getId(),
			));	
		}
	}	
	
	public function removeCoupon($coupon)
	{
		$t = \Cetera\Application::getInstance()->getTranslator();
		$d = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_coupon WHERE active=1 and code=?', array( $coupon ));
		if (!$d) throw new \Exception($t->_('Купон не найден'));
		if (isset($this->coupons[$d['id']])) {
            $this->checkId();
			self::getDbConnection()->delete('sale_cart_coupons', array(
				'coupon_id'  => $d['id'],
				'cart_id'  => $this->getId(),
			));	
			unset($this->coupons[$d['id']]);
		}		
	}
	
	public function setProductOption( $cart_id, $option_name, $option_value)
	{
		$data = $this->getDbConnection()->fetchAssoc('SELECT * FROM sale_cart_products WHERE cart_id=? and id=?', array($this->id, $cart_id));
		if ($data) {
			if (!$data['options']) {
				$data['options'] = array();
			}
			else {
				$data['options'] = unserialize($data['options']);
			}
			foreach($data['options'] as $id => $o) {
				if ($o['name'] == $option_name) {
					if ($option_value === null) {
						unset($data['options'][$id]);
					}
					else {
						$data['options'][$id]['value'] = $option_value;
					}					
					break;
				}
			}
			
			$this->getDbConnection()->update('sale_cart_products', array('options' => serialize($data['options'])), array('id' => $data['id']));
		}
		return $this;
	}		
	
	/*
	* добавить продукт в корзину или изменить количество
	* $quantity = 0 - удалить из корзины
	*/
	public function setProduct( $product, $quantity = 1, $offer = null, $options = null, $cart_id = null )
	{
		$s = \Cetera\Application::getInstance()->getSession();
		if (isset($s->saleOrderCreated)) {
			unset( $s->saleOrderCreated );
		}		
		
		$t = \Cetera\Application::getInstance()->getTranslator();
		
		if ($cart_id) {
			$data = $this->getDbConnection()->fetchAssoc('SELECT * FROM sale_cart_products WHERE cart_id=? and id=?', array($this->id, $cart_id));
			$product = $data['product_id'];
			$offer = $data['offer_id'];
			$where = array(
				'id' => $cart_id,
				'cart_id' => $this->id,
			);
		}
		else {
			$where = null;
		}
		
		if ( $product instanceOf Product ) {
			$pid = $product->id;
		}
		else {
			$pid = (int)$product;
			$product = \Sale\Product::getById($pid);
		}
		
		if (!$pid) throw new \Exception( $t->_('Не указан продукт') );
		
		if ( $product->hasOffers() && $offer ) {
			
			if ( $offer instanceOf Offer ) {
				$oid = $offer->id;
			}
			else {
				$oid = (int)$offer;
				if ($oid) $offer = \Sale\Offer::getById($oid);
			}			
			
			if (!$oid) throw new \Exception( $t->_('Не указано торговое предложение') );
			
		}
		else {
			$oid = 0;
		}
		
		if (!$where) {
			$where = array(
				'cart_id'    => $this->id,
				'product_id' => $pid,
				'offer_id'   => $oid,
			);
		}
		
		
		if ($quantity < 1)
		{
			$this->getDbConnection()->delete('sale_cart_products', $where);			
		}
		else
		{		
			if (!$product->canBuy((int)$quantity)) {
				throw new \Exception( $t->_('Отсутствует указанное количество') );
			}
			
			try
			{
				if ($cart_id) throw new \Exception('update');
                
                $this->checkId();
				
				$data = array(
					'cart_id'    => $this->id,
					'product_id' => $pid,
					'offer_id'   => $oid,
					'quantity'   => (int)$quantity,
                                        'options'    => '',
				);
				if (is_array($options)) {
					$data['options'] = serialize($options);
				}
				$this->getDbConnection()->insert('sale_cart_products', $data);				
			}
			catch (\Exception $e)
			{		
				$data = array(
					'quantity'   => (int)$quantity,
				);
				if (is_array($options)) {
					$data['options'] = serialize($options);
				}				
				$this->getDbConnection()->update('sale_cart_products', $data, $where);					
			}
		}
		return $offer?$offer:$product;
	}
	
   /*
	* добавить продукт в корзину
	*/
	public function addProduct( $product, $quantity = 1, $offer = null, $options = null )
	{
		if ((int)$quantity < 1) return $this;

		$t = \Cetera\Application::getInstance()->getTranslator();		
		
		if ( $product instanceOf Product ) {
			$pid = $product->id;
		}
		else {
			$pid = (int)$product;
			$product = \Sale\Product::getById($pid);
		}
		
		if (!$pid) throw new \Exception( $t->_('Не указан продукт') );
		
		if ( $product->hasOffers() && $offer ) {
			
			if ( $offer instanceOf Offer ) {
				$oid = $offer->id;
			}
			else {
				$oid = (int)$offer;
				if ($oid) $offer = \Sale\Offer::getById($oid);
			}			
			
			if (!$oid) throw new \Exception( $t->_('Не указано торговое предложение') );
			
		}
		else {
			$oid = 0;
		}
        
		$this->checkId();
	    $data = self::getDbConnection()->fetchArray('SELECT quantity FROM sale_cart_products WHERE cart_id = ? and product_id=? and offer_id=?', array( $this->id, $pid, $oid ));
		if ($data) {
			$quantity = $data[0] + $quantity;				
		}
		$this->setProduct( $pid, $quantity, $oid, $options );
		return $this;
	}

	public function getCurrency()
	{
		return Currency::getDefault();
	}
	
	/*
	* получить продукты в корзине
	*/	
	public function getProducts($discountPrice = true)
	{
        if (!$this->id) return [];
        
		$data = self::getDbConnection()->fetchAll('SELECT * FROM sale_cart_products WHERE cart_id = ?', array( $this->id ));
		$products = [];
		
		$totalSum = 0;
		
		foreach ($data as $value)
		{
			$q = $value['quantity'];
			
			if ($value['product_id']) {
				$prod = Product::getById( $value['product_id'] );
							
				if ($value['offer_id']) {
					$offer = Offer::getById( $value['offer_id'] );
					$offer->isInCart = true;
					$buyable = $offer;
				}	
				else {
					$offer = null;
					$prod->isInCart = true;
					$buyable = $prod;
				}
				
				if ($discountPrice) {
					$price = $buyable->discountPrice;
				}
				else {
					$price = $buyable->fullPrice;
				}
				
				$price = $this->getCurrency()->convert($price, $buyable->getCurrency());
				$name = $prod->name;
			}
			else {
				$prod = null;
				$offer = null;
				$price = isset($value['price'])?$value['price']:0;
				$name = isset($value['name'])?$value['name']:'-';
			}
			
			$products[] = [
				'product'    => $prod,
				'offer'      => $offer,
				'price'      => $price,
				'displayPrice' => $this->getCurrency()->format($price),
				'quantity'   => $q,
				'sum'        => $q * $price,
				'displaySum' => $this->getCurrency()->format($q * $price),
				'id'         => $value['id'],
				'name'       => $name,
				'options'    => $value['options']?unserialize($value['options']):null,
				'db_data'    => $value,
			];
			
			$totalSum += $q * $price;
		}
		
		if ($discountPrice) {
			$this->_totalSum = $totalSum;
		}
		
		return $products;
	}
	
	public function getProductsCount()
	{
		$data = self::getDbConnection()->fetchArray('SELECT SUM(A.quantity) FROM sale_cart_products A INNER JOIN sale_products B ON (A.product_id = B.id) WHERE A.cart_id = ?', array( $this->id ));
		return (int)$data[0];
	}	
	
	public function getTotal($display = false)
	{
		if ($this->_totalSum === null) {
			$this->getProducts();
		}
		if ($display) return $this->getCurrency()->format( $this->_totalSum );
		return $this->_totalSum;
	}	
	
	public function getDiscountTotal($display = false)
	{
		if ($this->_totalDiscountSum === null)
		{
			$this->_totalDiscountSum = 0;
			foreach ($this->getProducts() as $p) {
				$this->_totalDiscountSum += $this->getCurrency()->convert( $p['product']->getDiscount() * $p['quantity'], $p['product']->getCurrency() );
			}
		}
		if ($display) return $this->getCurrency()->format( $this->_totalDiscountSum );
		return $this->_totalDiscountSum;
	}	

	public function getDisplayTotal()
	{
		return $this->getCurrency()->format( $this->getTotal() );
	}
	
	public function clear()
	{
		self::getDbConnection()->delete('sale_cart_products', array(
			'cart_id' => $this->id,
		));	
		self::getDbConnection()->delete('sale_cart_coupons', array(
			'cart_id'  => $this->getId(),
		));			
		return $this;
	}
	
	public function delete()
	{
		$this->clear();
		$this->getDbConnection()->delete('sale_carts', array(
			'id' => $this->id,
		));	
	}	
	
}