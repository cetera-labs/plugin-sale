<?php
namespace Sale;

class WishList {
	
	use \Cetera\DbConnection;
	
	private $user = null;
	private $s;

	public static function get($user = null)
	{
        return new self($user);        	
	}
	
	private function __construct($user = null)
	{
        if (!$user) $user = \Cetera\Application::getInstance()->getUser();
		if ($user) {
			if (!is_a($user, '\Cetera\User'))
				$user = \Cetera\User::getById($user);
		}
		$this->user = $user;
		$this->s = \Cetera\Application::getInstance()->getSession();
		
		if ($this->user && isset($this->s->saleWishList) && count($this->s->saleWishList)) {
			foreach ($this->s->saleWishList as $pid) {
				$this->addProduct($pid);
			}
			unset($this->s->saleWishList);
		}
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
			$pid = (int)$product;
		}
		
		if (!$pid) throw new Exception(  $this->t->_('Не указан продукт') );
		
		if ($this->user) {		
			$data = self::getDbConnection()->fetchArray('SELECT * FROM sale_wishlist WHERE user_id = ? and product_id=?', array( $this->user->id, $pid ));
			if (!$data) {
					$this->getDbConnection()->insert(
                        'sale_wishlist',
                        [
                            'date_add' => new \DateTime(),
                            'user_id' => $this->user->id,
                            'product_id' => $pid,
                        ],
                        ['datetime']
                    );				
			}
		}
		else {
			if (!isset($this->s->saleWishList)) {
				$this->s->saleWishList = array();
			}
			$this->s->saleWishList[$pid] = $pid;
		}
		return $this;
	}	
	
	public function removeProduct( $product ) {
		if ( $product instanceOf Product ) {
			$pid = $product->id;
		}
		else {
			$pid = (int)$product;
		}
		
		if ($this->user) {
			$this->getDbConnection()->delete('sale_wishlist', array(
				'user_id' => $this->user->id,
				'product_id' => $pid,
			));
		}
		else {
			if (isset($this->s->saleWishList)) {
				if (isset($this->s->saleWishList[$pid])) {
					unset($this->s->saleWishList[$pid]);
				}
			}			
		}
		return $this;		
	}
	
	public function checkProduct( $product ) {
		if ( $product instanceOf Product ) {
			$pid = $product->id;
		}
		else {
			$pid = (int)$product;
		}

		if ($this->user) {
			$data = self::getDbConnection()->fetchColumn('SELECT COUNT(*) FROM sale_wishlist WHERE user_id = ? and product_id = ?', [ $this->user->id, $pid ]);
			return (boolean)$data;
		}
		else {
			if (isset($this->s->saleWishList)) {
				if (isset($this->s->saleWishList[$pid])) {
					return TRUE;
				}
			}	
			return FALSE;
		}		
	}
	
	/*
	* получить продукты в списке
	*/	
	public function getProducts()
	{
		if ($this->user) {
            $products = new \Cetera\Iterator\DynamicObject( \Sale\Product::getObjectDefinition() );
            $products->getQuery()
                ->select('main.*', 'wish.date_add')
                ->innerJoin('main', 'sale_wishlist', 'wish', 'main.id = wish.product_id')
                ->where('wish.product_id=:user')
                ->orderBy('wish.date_add', 'DESC')
                ->setParameter('user', $this->user->id);
		} 
		elseif (isset($this->s->saleWishList)) {
            $products = new \Cetera\Iterator\Base();
			foreach ($this->s->saleWishList as $pid) {
				$products->append(\Sale\Product::getById( $pid ));
			}			
		}
		return $products;
	}
	
	public function getProductsCount()
	{
		if ($this->user) {
			$data = self::getDbConnection()->fetchArray('SELECT count(B.id) FROM sale_wishlist A INNER JOIN sale_products B ON (A.product_id = B.id) WHERE A.user_id = ?', array( $this->user->id ));
			return (int)$data[0];
		} 
		elseif (isset($this->s->saleWishList)) {
			return count($this->s->saleWishList);
		}
		else {
			return 0;
		}
	}
	
	public function clear()
	{
		if ($this->user) {
			$this->getDbConnection()->delete('sale_wishlist', array(
				'user_id' => $this->user->id,
			));	
		}
		if (isset($this->s->saleWishList)) {
			unset($this->s->saleWishList);
		}
		return $this;
	}	
	
}