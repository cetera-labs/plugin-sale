<?php
namespace Sale;

class WidgetOrder extends \Cetera\Widget\Templateable
{

	private $order = null;
	private $order_from_cart = false;
	
	protected function initParams()
	{
		$this->_params = array(
			'cart_url' => '/cart/',
			'catalog_url'  => false,
			'template'     => 'default.twig',
			'widgetTitle'  => '<div class="row column"><h1>'.$this->t->_('Оформить заказ').'</h1></div>',
		); 
		
	}	
    
	public function getHiddenFields()
	{
		$str  = '<input type="hidden" name="order-create" value="'.$this->getUniqueId().'" />';
		return $str;
	}    
	
	protected function init()
	{	
	    $this->application->addScript('/plugins/sale/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/cms/plugins/sale/js/common.js');
		$this->application->addScript('/cms/plugins/sale/js/jquery-ui.min.js');
		$this->application->addCSS('/cms/plugins/sale/js/jquery-ui.min.css');
					
		if (isset($this->application->getSession()->saleOrderCreated)) {
			$this->order = \Sale\Order::getById( $this->application->getSession()->saleOrderCreated );
			//unset( $this->application->getSession()->saleOrderCreated );
			return;
		}
			
		if (!$this->getCart()->getProductsCount()) {
			$this->setParam('cart_is_empty', 1);
		}
		
		if ( isset($_POST['order-create']) && $_POST['order-create'] == $this->getUniqueId() ) {
			$order = $this->getOrder();
			$order->setParams($_POST);

			$user = $this->application->getUser();
			if (!$user) {				
				$user = $this->createUser(); 
			}
			
			$order->setUser($user)
			      ->save();
				  
			if ($this->order_from_cart) $this->getCart()->clear();			
			
			if (!$this->getParam('ajaxCall')) {
				$this->application->getSession()->saleOrderCreated = $this->getOrder()->getId();
				header('Location: '.$_SERVER['REQUEST_URI']);
				die();
			}
		}		
		
	}
	
	protected function createUser()
	{
		$order = $this->getOrder();
		$login = $order->getLogin();
		while (\Cetera\User::getByLogin($login)) {
			$login = 'user_'.time();
		}
		
		$pass = \Cetera\User::generatePassword();
		$user = \Cetera\User::create();
		$user->setFields(array(
			'login'    => $login,
			'name'     => $order->getName(),
			'email'    => $order->getEmail(),
			'phone'    => $order->getPhone(),
			'password' => $pass,
		));
		$user->save();
		$this->application->getAuth()->authenticate(new \Cetera\UserAuthAdapter( array(
			'login' => $login,
			'pass'  => $pass,
		) )); 
		return $user;
	}	

	public function getCart()
	{
		return \Sale\Cart::get();
	}
	
	public function getOrder()
	{
		if (!$this->order) {
			
			if ($this->getParam('products')) {
				$products = $this->getParam('products');
				$this->order = \Sale\Order::create();
				if ( is_int($products) ) {
					$this->order->addProduct( $products, 1 );
				}
				elseif ( is_array($products) ) {
					foreach ($products as $p) {
						if ( is_array($p) ) {
							if (!isset($p['quantity'])) $p['quantity'] = 1;
							$this->order->addProduct( $p['product_id'], $p['quantity'], $p['offer_id'] );
						}
						elseif ( is_int($p) ) {
							$this->order->addProduct( $p, 1 );
						}
					}
				}
			}
			else {
				$this->order = \Sale\Order::make( $this->getCart() );
				$this->order_from_cart = true;
			}
		}
		return $this->order;
	}
	
	public function getProps($person_id)
	{
		$props = \Sale\Order::getAvailableProps($person_id);
		$user = $this->application->getUser();
		if ($user) {
			
			$last_props = array();
			$data = self::getDbConnection()->fetchAll('SELECT * FROM `sale_order_props_value` WHERE order_id IN (SELECT id FROM `sale_orders` WHERE user_id='.$user->id.') ORDER BY order_id');
			foreach ($data as $d) {
				$last_props[$d['order_props_id']] = $d['value'];
			}
			
			foreach ($props as $key => $value) {
				
				if (isset($last_props[$value['id']])) {
					$props[$key]['default_value'] = $last_props[$value['id']];
				}
				
				if ($value['is_email'] && $user->email) $props[$key]['default_value'] = $user->email;
				if ($value['is_login'] && $user->name) $props[$key]['default_value'] = $user->name;
				if ($value['is_phone'] && $user->phone) $props[$key]['default_value'] = $user->phone;
			}
		}
		return $props;
	}
}