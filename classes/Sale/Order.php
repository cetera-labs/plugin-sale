<?php
namespace Sale;

class Order {
	
	use \Cetera\DbConnection, \Cetera\Traits\Extendable;
	
	// заказ сформирован
	const STATUS_CREATED    = 0;
	// заказ проверен менеджером
	const STATUS_CHECKED    = 1;
	// заказ в обработке
	const STATUS_PROCESSING = 2;
	// заказ готов к отправке, выдаче
	const STATUS_READY      = 3;
	// заказ готов к отправлен
	const STATUS_SENT       = 4;
	// заказ доставлен
	const STATUS_DONE       = 5;
	// заказ отменен
	const STATUS_CANCELLED  = 6;
	
	// ожидает оплаты
	const PAY_WAIT    = 0;
	// оплачен
	const PAY_PAID    = 1;
	// отменен
	const PAY_CANCEL  = 2;
	// деньги возвращены
	const PAY_REFUND  = 3;
	
	protected static $availableStatuses = [
		self::STATUS_CREATED,
		self::STATUS_CHECKED,
		self::STATUS_PROCESSING,
		self::STATUS_READY,
		self::STATUS_SENT,
		self::STATUS_DONE,
		self::STATUS_CANCELLED
	];
	
	protected static $availablePayStatuses = [
		self::PAY_WAIT,
		self::PAY_PAID,
		self::PAY_CANCEL,
		self::PAY_REFUND
	];	
	
	protected $id = 0;
	
	protected $delivery_id = 0;
	protected $delivery_cost = null;
	protected $delivery_note = null;
	protected $delivery = null;
	
	protected $payment_id = 0;
	protected $payment_data = null;
	protected $payment_gateway = null;
	
	protected $user_id = 0;
	
	// дата заказа
	protected $date = null;
	
	// статус заказа
	protected $status = 0;
	
	// статус оплаты
	protected $paid   = 0;
	
	protected $person_type_id = 1;
	
	protected $props = null;
	protected $products = null;
	protected $cart_id = null;
	protected $coupons = null;
	
	protected $currency = null;
	
	// комментарии менеджеров магазина к заказу
	protected $note = null;
	
	protected static $person_types = null;
	protected static $person_props = [];
	
	public $initialData = [];
		
    public function __get($name)
    {
    
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) return $this->$method();
        
        if (property_exists($this, $name)) return $this->$name;
    
        throw new \LogicException("Property {$name} is not found");
    }	
	
	public static function enum()
	{
		return Iterator\Order::create();
	}
	
	public static function t()
	{
		return \Cetera\Application::getInstance()->getTranslator();
	}	
	
	public function __construct()
	{
		$this->status = static::$availableStatuses[0];
		$this->paid = static::$availablePayStatuses[0];
	}		
	
	public static function make($cart)
	{
		if (!$cart->getProductsCount()) {
			throw new \Exception( self::t()->_('Ваша корзина пуста!'));
		}
		$order = self::create();
		$order->products = $cart->getProducts();
		$order->cart_id = $cart->getId();
		$order->currency = $cart->getCurrency();
		$order->coupons = $cart->getCoupons();		
		return $order;
	}
	
	public static function getById($id)
	{
		$data = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_orders WHERE id=?',array($id));
		if (!$data) throw new \Exception( sprintf( self::t()->_('Заказ №%s не найден') , $id) );
		$order = self::create();
		$order->setData($data);
		return $order;
	}

    public static function getByTransaction($txn_id)
    {
        $oid = self::getDbConnection()->fetchColumn('SELECT order_id FROM sale_payment_transactions WHERE transaction_id=?',[$txn_id]);
        if (!$oid) throw new \Exception( sprintf( self::t()->_('Транзакция %s не найдена') , $txn_id) );
        return self::getById($oid);
    }
	
	public  function getId()
	{
		return $this->id;
	}

    public function refund( $items = null ) {
        $gateway = $this->getPaymentGateway();
        if (!$gateway->isRefundAllowed()) {
            throw new \Exception('Возврат невозможен');
        }
        $gateway->refund($items);
        
        $this->getProducts();
        if ($items == null) {
            foreach ($this->products as $key => $product) {
                $this->products[$key]['sum_refund'] = $this->products[$key]['quantity'] * $this->products[$key]['price'];
                $this->products[$key]['quantity'] = 0;
            }
            $this->setPaid(self::PAY_REFUND)->save();
        } 
        else {
            $refunded = [];
            foreach ($items as $item) {
                if ($item['quantity_refund'] <= 0) continue;
                $refunded[$item['id']] = $item;
            }
            foreach ($this->products as $key => $product) {
                if (!isset($refunded[$product['id']] )) continue;
                $this->products[$key]['sum_refund'] += $refunded[$product['id']]['price'] * $refunded[$product['id']]['quantity_refund'];
                $this->products[$key]['quantity'] -= $refunded[$product['id']]['quantity_refund'];
            }
            $this->save();
        }
    }
	
	public static function getStatuses()	
	{
		$data = [];
		foreach (static::$availableStatuses as $id) $data[] = [
			'id'   => $id,
			'name' => static::getStatusTextStatic( $id )
		];
		return $data;
	}
	
	public static function getStatusTextStatic($value)
	{
		switch ($value)
		{
			case self::STATUS_CREATED:   return self::t()->_('сформирован');
			case self::STATUS_CHECKED:   return self::t()->_('проверен');
			case self::STATUS_PROCESSING:return self::t()->_('в обработке');
			case self::STATUS_READY:     return self::t()->_('готов к отправке, выдаче');
			case self::STATUS_SENT:      return self::t()->_('отправлен');
			case self::STATUS_DONE:      return self::t()->_('доставлен');
			case self::STATUS_CANCELLED: return self::t()->_('отменен');			
		}
	}	
	
	public function getStatusText()
	{
		return static::getStatusTextStatic($this->status);
	}	
	
	public function getStatus()
	{
		return $this->status;
	}
    
	public function getPayStatus()
	{
		return $this->paid;
	}    
	
	public static function getPayStatuses()	
	{
		$data = [];
		foreach (static::$availablePayStatuses as $id) $data[] = array(
			'id'   => $id,
			'name' => static::getPayTextStatic( $id )
		);
		return $data;
	}

	public static function getPayTextStatic($value)
	{
		switch ($value)
		{
			case self::PAY_WAIT:   return self::t()->_('не оплачен');
			case self::PAY_PAID:   return self::t()->_('оплачен');
			case self::PAY_CANCEL: return self::t()->_('платеж отменен');
			case self::PAY_REFUND: return self::t()->_('деньги возвращены');			
		}
	}	
	
	public function getPayText()
	{
		return static::getPayTextStatic($this->paid);
	}

	public function setProducts( $products )
	{
		$this->products = $products;
		return $this;
	}
	
	public function getProducts()
	{
		if (!$this->products)
		{
			$data = self::getDbConnection()->fetchAll('SELECT * FROM sale_order_products WHERE order_id = ?',[$this->id]);
			$products = array();
			foreach ($data as $value)
			{
				$q = (int)$value['quantity'];
				$p = (float)$value['price'];

				try {
					if ($value['product_id']) {
						$prod = Product::fetch( $value['product_id'] );
					}
					else {
						$prod = null;
					}
				}
				catch (\Exception $e) {
					$prod = null;
				}
				
				try {
					if ( $value['offer_id'] ) {
						$offer = Offer::fetch( $value['offer_id'] );
					}
					else {
						$offer = null;
					}
				}
				catch (\Exception $e) {
					$offer = null;
				}
                
                $name = $value['product_name'];
                if ($prod) {
                    $name = $prod->name;
                    if ($offer) {
                        $name .= ' > '.$offer->name;
                    }
                }
				
				$products[] = array(
					'product'      => $prod,
					'offer'        => $offer,
					'price'        => $p,
					'displayPrice' => $this->getCurrency()->format($p),
					'quantity'   => $q,
                    'unit'       => isset($value['unit'])?$value['unit']:'',
					'sum'        => $q * $p,
                    'sum_refund' => isset($value['sum_refund'])?(int)$value['sum_refund']:0,
					'displaySum' => $this->getCurrency()->format($q * $p),
					'id'         => $value['product_id'].'-'.$value['offer_id'],
					'name'       => $name,
					'bo_url'     => /*$prod?$prod->getBoUrl( true ):*/$value['product_name'],
					'options'    => $value['options']?unserialize($value['options']):null,
				);
			}	
			$this->products = $products;
		}
		return $this->products;
	}
	
	public function getProductsTable()
	{
		$list = '<table><thead><tr><th>'.self::t()->_('Наименование').'</th><th>'.self::t()->_('Цена').'</th><th>'.self::t()->_('Кол-во').'</th><th>'.self::t()->_('Стоимость').'</th></tr></thead><tbody>';
		foreach ($this->getProducts() as $p)
		{
			$list .= '<tr><td>'.$p['name'].'</td><td>'.$p['price'].'</td><td>'.$p['quantity'].'</td><td>'.$p['sum'].'</td>';
		}			
		$list .= '</tbody></table>';
		return $list;
	}
	
	public function getProperty($name)
	{
		if ((int)$name) {
			return isset($this->props[$name])?$this->props[$name]:null;
		}
		else {
			$p = $this->getProps();
			return isset($p[$name])?$p[$name]['value']:null;
		}
	}	
	
	public function setProperty($name, $value)
	{
		if ((int)$name) {
			$this->props[$name] = $value;
		}
		else {
			$p = $this->getProps();
			$this->props[ $p[$name]['id'] ] = $value;
		}
		return $this;
	}	

	public function setData($data)
	{
		$this->initialData = $data;
		return $this->setParams($data);
	}	
	
	public function setParams($params)
	{
		if (isset($params['id'])) {
			$this->id = (int)$params['id'];
		}		
		if (isset($params['user_id'])) {
			$this->user_id = $params['user_id'];
		}			
		if (isset($params['date'])) {
			$this->date = $params['date'];
		}			
		
		if (isset($params['person_type'])) {
			$this->setPersonType($params['person_type']);
		}		
		
		if (isset($params['delivery_method'])) {
			$this->setDelivery($params['delivery_method']);
		}
		if (isset($params['delivery_id'])) {
			$this->setDelivery($params['delivery_id']);
		}		
		if (isset($params['delivery_cost'])) {
			$this->delivery_cost = $params['delivery_cost'];
		}
		if (isset($params['delivery_note'])) {
			$this->delivery_note = $params['delivery_note'];
		}		
		
		if (isset($params['payment_method'])) {
			$this->setPayment($params['payment_method']);
		}
		if (isset($params['payment_id'])) {
			$this->setPayment($params['payment_id']);
		}		
		
		if (isset($params['status'])) {
			$this->setStatus($params['status']);
		}
		if (isset($params['paid'])) {
			$this->setPaid($params['paid']);
		}
		if (isset($params['person_type_id'])) {
			$this->person_type_id = $params['person_type_id'];
		}	
		if (isset($params['currency'])) {
			$this->currency = Currency::getByCode($params['currency']);
		}	
		
		if (array_key_exists('note', $params)) {
			$this->note = $params['note'];
		}			
		
		if (isset($params['props'])) {
			$this->props = $params['props'];
		}
			
		return $this;
	}
	
	public function getProps()
	{
		if ($this->props === null)
		{
			$data = self::getDbConnection()->fetchAll('SELECT * FROM sale_order_props_value WHERE order_id=?', array($this->id));
			$this->props = array();
			foreach ($data as $d)
			{
				$this->props[ (int)$d['order_props_id'] ] = $d['value'];
			}
		}
		$ret = $this->getAvailableProps( $this->person_type_id );
		foreach ($ret as $id => $r) {
			$ret[$id]['value'] = isset($this->props[ $r['id'] ])?$this->props[ $r['id'] ]:null;
		}
		return $ret;
	}	
	
	public function setStatus($value)
	{
		$this->status = (int)$value;
		return $this;
	}

	public function setPaid($value)
	{
		$this->paid = (int)$value;
		return $this;
	}	
	
	public function setPayment($payment)
	{
		$this->payment_id = (int)$payment;
		return $this;
	}
	
	public function getPaymentId()
	{
		return $this->payment_id;
	}	

	public function getPaymentData()
	{
		if (!$this->payment_data) {
			$this->payment_data = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_payment WHERE id=?', array($this->payment_id));
		}
		return $this->payment_data;
	}

	public function getPaymentGateway()
	{
		if (!$this->payment_gateway) {
			$data = $this->getPaymentData();
			if (!$data) return false;
		
			$class = $data['gateway'];
			if ($class && Payment::isGatewayExists($class)) {
				$this->payment_gateway = new $class(json_decode($data['gateway_params'], true), $this);
			}				
		}
		return $this->payment_gateway;
	}	
	
	public function setDelivery($delivery)
	{
		$this->delivery_id = (int)$delivery;
		$this->delivery = null;
		return $this;
	}	
	
	public function getDeliveryId()
	{
		return $this->delivery_id;
	}

	public function getDelivery()
	{
		if (!$this->delivery) {
			$this->delivery = new Delivery( self::getDbConnection()->fetchAssoc('SELECT * FROM sale_delivery WHERE id=?', array($this->delivery_id)), $this );
		}
		return $this->delivery;
	}
	
	public function getDeliveryData()
	{
		return $this->getDelivery();
	}
	
	public function getDeliveryCost($display = false)
	{
		if ($this->delivery_cost === null) {
			if ($this->getDeliveryId()) {
				$this->getDeliveryData();
				$this->delivery_cost = $this->delivery->getCost();
			}
			else {
				$this->delivery_cost = 0;
			}
		}
		if ($display) return $this->getCurrency()->format( $this->delivery_cost );
		return $this->delivery_cost;
	}
	
	public function getDeliveryNote()
	{
		if ($this->delivery_note === null)
		{
			$this->getDeliveryData();
			$this->delivery_note = $this->delivery->getNote();
		}
		return $this->delivery_note;
	}	
	
	public function getTotal($display = false)
	{
		if ($display) return $this->getCurrency()->format( $this->getDeliveryCost() + $this->getProductsCost() );
		return $this->getDeliveryCost() + $this->getProductsCost();
	}
	
	public function getProductsCost($display = false)
	{
		$sum = 0;
		foreach( $this->getProducts() as $p )
		{
			$sum += $p['quantity'] * $p['price'];
		}
		if ($display) return $this->getCurrency()->format( $sum );
		return $sum;		
	}
	
	public function getPaymentMethods( $all = false )
	{
		if ($all) {
			$data = self::getDbConnection()->fetchAll('SELECT * FROM sale_payment where active=1 ORDER BY tag');
		}
		elseif ($this->delivery_id && $this->person_type_id) {
			$data = self::getDbConnection()->fetchAll('
				SELECT B.* 
				FROM sale_payment B
					INNER JOIN sale_delivery_payment A ON (A.payment_id = B.id) 
					INNER JOIN sale_payment_person_type C ON (C.payment_id = B.id) 
				WHERE B.active=1 and A.delivery_id=? and C.person_type_id=? GROUP BY B.id ORDER BY B.tag', array($this->delivery_id, $this->person_type_id));
		}
		elseif($this->delivery_id) {
			$data = self::getDbConnection()->fetchAll('
				SELECT B.* 
				FROM sale_payment B
					INNER JOIN sale_delivery_payment A ON (A.payment_id = B.id)  
				WHERE B.active=1 and A.delivery_id=? GROUP BY B.id ORDER BY B.tag', array($this->delivery_id));
		}
		elseif($this->person_type_id) {
			$data = self::getDbConnection()->fetchAll('
				SELECT B.* 
				FROM sale_payment B
					INNER JOIN sale_payment_person_type C ON (C.payment_id = B.id) 
				WHERE B.active=1 and C.person_type_id=? GROUP BY B.id ORDER BY B.tag', array($this->person_type_id));
		}		
		return $data;		
	}
	
	public function getDeliveryMethods( $all = false )
	{
		if ($all)
		{		
			$data = self::getDbConnection()->fetchAll('SELECT * FROM sale_delivery where active=1 ORDER BY tag');
		}
		elseif ($this->payment_id && $this->person_type_id)
		{
			$data = self::getDbConnection()->fetchAll('
				SELECT A.*
				FROM sale_delivery A
					INNER JOIN sale_delivery_payment B ON (A.id = B.delivery_id) 
					INNER JOIN sale_payment_person_type C ON (B.payment_id = C.payment_id) 
				WHERE A.active=1 and B.payment_id=? and C.person_type_id=?
				GROUP BY A.id ORDER BY A.tag
			', array($this->payment_id, $this->person_type_id));
		}
		elseif ($this->payment_id)
		{
			$data = self::getDbConnection()->fetchAll('
				SELECT A.*
				FROM sale_delivery A
					INNER JOIN sale_delivery_payment B ON (A.id = B.delivery_id) 
				WHERE A.active=1 and B.payment_id=?
				GROUP BY A.id ORDER BY A.tag
			', array($this->payment_id));			
		}
		elseif ($this->person_type_id)
		{
			$data = self::getDbConnection()->fetchAll('
				SELECT A.*
				FROM sale_delivery A
					INNER JOIN sale_delivery_payment B ON (A.id = B.delivery_id) 
					INNER JOIN sale_payment_person_type C ON (B.payment_id = C.payment_id) 
				WHERE A.active=1 and C.person_type_id=?
				GROUP BY A.id ORDER BY A.tag
			', array($this->person_type_id));			
		}
		$methods = array();
		foreach($data as $d) $methods[] = new Delivery($d, $this);
		return $methods;
	}	
	
	public function setUser($user)
	{
		$this->user_id = $user->id;
		return $this;
	}
	
	public function getUser()
	{
		try {
			return \Cetera\User::getById($this->user_id);
		}
		catch (\Exception $e) {
			return false;
		}
	}	
	
	public function getEmail()
	{
		$this->getProps();
		foreach (self::getAvailableProps($this->person_type_id) as $p)
		{
			if ($p['is_email']) return $this->getProperty($p['id']);
		}
	}
	
	public function getPhone()
	{
		$this->getProps();
		foreach (self::getAvailableProps($this->person_type_id) as $p)
		{
			if ($p['is_phone']) return $this->getProperty($p['id']);
		}		
	}
	
	public function getLogin()
	{
		if ($this->getUser()) return $this->getUser()->login;
		$login = str_replace( ' ','_', $this->getName() );
		if (!$login) 
		{
			$login = 'user_'.(($this->cart_id)?$this->cart_id:time());
		}
		return $login;
	}
	
	public function getName()
	{
		$this->getProps();
		$res = array();
		foreach (self::getAvailableProps($this->person_type_id) as $p)
		{
			if ($p['is_login']) 
				if ($this->getProperty($p['id'])) 
					$res[] = $this->getProperty($p['id']);
		}
		return implode(' ', $res);
	}
	
	public function getDate()
	{
		return $this->date;
	}

	protected function getMailEventParams()
	{
		return array(
			'sale'   => array(
				'email'   => \Sale\Setup::configGet('sale_email'),	
			),
			'order'  => $this,		
			'server' => \Cetera\Application::getInstance()->getServer(),
			'message' => 'Order #'.$this->id.' ['.$this->getName().' ('.$this->getEmail().')'.']',
		);
	}	

	public function delete()
	{
		self::getDbConnection()->delete('sale_order_products', array('order_id' => $this->id));
		self::getDbConnection()->delete('sale_order_props_value', array('order_id' => $this->id));
		self::getDbConnection()->delete('sale_orders', array('id' => $this->id));			
	}		

	public function save()
	{
		if (!$this->user_id) throw new \Exception( self::t()->_('Не указан пользователь для заказа') );
		
		$data = [];
		
		$data['status'] = $this->status;
		$data['person_type_id'] = $this->person_type_id;
		$data['payment_id'] = $this->payment_id;
		$data['delivery_id'] = $this->delivery_id;
		$data['delivery_cost'] = $this->getDeliveryCost();
		$data['delivery_note'] = $this->getDeliveryNote();
		$data['user_id'] = $this->user_id;
		$data['paid'] = $this->paid;
		$data['currency'] = $this->getCurrency()->code;
		$data['note'] = $this->note;
		
		$new_order = false;
		if (!$this->id) {
			$new_order = true;
			self::getDbConnection()->insert('sale_orders', $data);
			$this->id = (int)self::getDbConnection()->lastInsertId();
			self::getDbConnection()->update('sale_orders', array('date' => new \DateTime()), array('id' => $this->id), array('datetime'));	
			
			if (!$this->date){
				$this->date = date('Y-m-d');
			}
			
			if ( \Sale\Setup::configGet( 'use_quantity' ) ) {
				foreach ($this->getProducts() as $p) {
					if (!$p['product']) continue;
					$p['product']->quantity = $p['product']->quantity - $p['quantity'];
					$p['product']->save();
				}
			}
			
			if (is_array($this->coupons)) {
				foreach ($this->coupons as $c) {
					self::getDbConnection()->insert('sale_order_coupons', array(
						'coupon_id'  => $c['id'],
						'order_id'  => $this->getId(),
					));	
					if ($c['mode'] == 1) {
						// одноразовый
						self::getDbConnection()->update('sale_coupon', array(
							'active'  => 0
						), array(
							'id' => $c['id']
						));							
					}
				}
			}
			
			$user = \Cetera\User::getById($this->user_id);
		}
		else {
			$this->getProducts();
			self::getDbConnection()->update('sale_orders', $data, array('id' => $this->id));
			self::getDbConnection()->delete('sale_order_products', array('order_id' => $this->id));
			if ($this->props !== null) {
				self::getDbConnection()->delete('sale_order_props_value', array('order_id' => $this->id));
			}
		}
		self::getDbConnection()->update('sale_orders', array('date_update' => new \DateTime()), array('id' => $this->id), array('datetime'));	
		
		foreach ($this->getProducts() as $p)
		{
			$d = array(
				'order_id'     => (int)$this->id,
				'product_id'   => $p['product']->id,
				'product_name' => $p['name'],
				'quantity'     => $p['quantity'],
				'price'        => $p['price'],
                'unit'         => $p['unit'],
                'sum_refund'   => isset($p['sum_refund'])?(int)$p['sum_refund']:0,
				'options'      => is_array($p['options'])?serialize($p['options']):'',
				'offer_id'     => $p['offer']?$p['offer']->id:0,
			);

			self::getDbConnection()->insert('sale_order_products', $d);
		}
		$this->products = null;
		
		if ($this->props !== null) {	
			foreach ($this->props as $id => $value) {
				if (is_array($value)) {
					$id = $value['id'];
					$value = $value['value'];
				}
				if ($value === null) continue;
				self::getDbConnection()->insert('sale_order_props_value', array(
					'order_id' => $this->id,
					'order_props_id' => $id,
					'value' => $value,
				));
			}
		}
		
		if ($new_order) {
			\Cetera\Event::trigger( 'SALE_NEW_ORDER', $this->getMailEventParams() );
			\Cetera\Event::trigger( 'SALE_ORDER_CREATE', $this->getMailEventParams() );
		}
		else {		
			if (isset($this->initialData['status']) && $this->status != $this->initialData['status']) {
				
				\Cetera\Event::trigger( 'SALE_ORDER_STATUS_CHANGED', $this->getMailEventParams() );
				
				if ($this->status == self::STATUS_CANCELLED ) {
					\Cetera\Event::trigger( 'SALE_ORDER_CANCEL', $this->getMailEventParams() );
				}	

				if ($this->status == self::STATUS_CHECKED ) {
					\Cetera\Event::trigger( 'SALE_ORDER_CHECKED', $this->getMailEventParams() );
				}	
			}
			
			if (isset($this->initialData['paid']) && $this->paid != $this->initialData['paid']) {
				if ($this->paid == self::PAY_PAID ) {
					\Cetera\Event::trigger( 'SALE_ORDER_PAID', $this->getMailEventParams() );
				}
			}
		}		

		$this->initialData = $data;
	}
	
	public function setPersonType($id)
	{
		$this->person_type_id = (int)$id;
	}
	
	public function getPersonType()
	{
		return $this->person_type_id;
	}	
	
	public static function getPersonTypes()
	{
		if (self::$person_types === null)
			self::$person_types = self::getDbConnection()->fetchAll('SELECT * FROM sale_person_type WHERE active=1 ORDER BY sort');
		return self::$person_types;
	}
	
	public static function getAvailableProps($person_id)
	{
		$person_id = (int)$person_id;
		if (!isset(self::$person_props[$person_id]))
		{
			$data = self::getDbConnection()->fetchAll('
				SELECT A.*, B.name as group_name, B.id as group_id
				FROM sale_order_props A LEFT JOIN sale_order_props_group B ON (A.group_id = B.id)
				WHERE A.person_type_id='.$person_id.' and A.active>0 ORDER BY B.sort, A.sort
			');				
			self::$person_props[$person_id] = array();		
			foreach ($data as $d)
			{
				self::$person_props[$person_id][$d['alias']] = $d;
			}
		}
		return self::$person_props[$person_id];
	}	
	
	public function isCancelable()
	{
		return !in_array($this->status, array(self::STATUS_CANCELLED,self::STATUS_DONE,self::STATUS_SENT));
	}
    
	public function isPaid()
	{
		return $this->paid == self::PAY_PAID;
	}     
	
	public function canBePaid()
	{
		
		$user = \Cetera\Application::getInstance()->getUser();
		// неавторизованный пользователь не может ничего оплачивать
		if (!$user) return false;
		
		// чужие заказы тоже нельзя оплачивать
		if ($this->user_id != $user->id) return false;
		
		// отмененные, выполненные заказы тоже нельзя оплачивать
		if (in_array($this->status, array(self::STATUS_CANCELLED,self::STATUS_DONE))) return false;
		
		// оплатить можно только неоплаченный заказ
		if ($this->paid != self::PAY_WAIT) return false;
		
		// без платежного шлюза нельзя оплачивать
		if (!$this->getPaymentGateway()) return false;
	
		return true;
	}	

	public function getPayUrl( $return = '', $payParams = [] )
	{
		if (!$this->canBePaid()) return false;
		return '/plugins/sale/pay.php?order='.$this->id.'&return='.urlencode($return).'&params='.urlencode(serialize($payParams));
	}
	
	public function getCurrency()
	{
		if (!$this->currency) {
			$this->currency = Currency::getDefault();
		}
		return $this->currency;
	}	

	public function checkPaymentAmountAndCurrency($amount, $currency)
	{
		if ($this->getCurrency() != $currency) throw new \Exception( self::t()->_('Неправильная валюта платежа') );
		if ($this->getTotal() != $amount) throw new \Exception( self::t()->_('Неправильная сумма платежа') );
	}
	
	public function checkUser($user_id)
	{
		if ($this->user_id != (int)$user_id) throw new \Exception( self::t()->_('Плательщик отличается от пользователя заказа') );
	}

	public function paymentSuccess()
	{
		$this->setPaid(self::PAY_PAID)->save();
	}	
	
   /*
	* добавить продукт к заказу
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
		
	    $data = self::getDbConnection()->fetchArray('SELECT quantity FROM sale_order_products WHERE order_id = ? and product_id=? and offer_id=?', array( $this->id, $pid, $oid ));
		if ($data) {
			$quantity = $data[0] + $quantity;				
		}
		$this->setProduct( $pid, $quantity, $oid, $options );
		return $this;
	}

    public function removeProduct( $product, $offer = 0 )
	{	
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
		
		$itemID = $pid.'-'.$oid;

		$this->getProducts();
		
		$exists = -1;
		foreach ($this->products as $key => $value) {
			if ($value['id'] == $itemID) {
				$exists = $key;
				break;
			}
		}

        if ($exists >= 0) unset($this->products[$exists]);
    }
	
	/*
	* добавить продукт к заказу или изменить количество
	* $quantity = 0 - удалить из заказа
	*/
	public function setProduct( $product, $quantity = 1, $offer = 0, $options = null, $price = null, $sum_refund = null )
	{	
		
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
		
		$itemID = $pid.'-'.$oid;
		
		$this->getProducts();
		
		$exists = -1;
		foreach ($this->products as $key => $value) {
			if ($value['id'] == $itemID) {
				$exists = $key;
				break;
			}
		}
		
		if ($quantity <= 0) {
			$quantity = 0;		
		}
        else {
            if (!$product->canBuy((int)$quantity)) {
                throw new \Exception( $t->_('Отсутствует указанное количество') );
            }
        }
        
        if ($price === null) {
            $price = $offer?$offer->price:$product->price;
        }
        
        if ($exists >= 0) {
            $this->products[$exists]['quantity'] = $quantity;
            $this->products[$exists]['price'] = $price; 
            if ($sum_refund !== null) {
                $this->products[$exists]['sum_refund'] = $sum_refund;
            }
        }
        else {
            $this->products[] = [
                'product'    => $product,
                'offer'      => $offer,
                'price'      => (float)$price,
                'displayPrice' => $this->getCurrency()->format($price),
                'quantity'   => $quantity,
                'sum'        => $quantity * $price,
                'displaySum' => $this->getCurrency()->format($quantity * $price),
                'id'         => $itemID,
                'options'    => $options,
                'name'       => $product->name.($offer?', '.$offer->name:''),
                'bo_url'     => '',
                'unit'       => $product->unit,
                'sum_refund' => ($sum_refund !== null)?$sum_refund:0,
            ];
        }		
		
		//$this->save();
		//$this->products = null;
				
		return $this;
	}

    public function asArray($fields = null)
    {
        $refund = false;
        $g = $this->getPaymentGateway();
        if ($g) {
            $refund = $g->isRefundAllowed();
        }
        
		$data = [
			'id'         => (int)$this->id,
			'date'       => $this->date,
			'total'      => $this->getTotal(),
			'user_id'    => $this->user_id,
			'status_text'=> $this->getStatusText(),
			'paid_text'  => $this->getPayText(),
			'status'     => $this->status,
			'paid'       => $this->paid,		
			'products'   => $this->getProducts(),
			'props'      => array_values($this->getProps()),
			'buyer'      => $this->getName().' ('.$this->getEmail().')',
			'payment_id' => $this->getPaymentId(),
			'payment_data' => $this->getPaymentData(),
            'payment_refund_allowed' => $refund,
			'delivery_id'  => $this->getDeliveryId(),
			'delivery_data' => $this->getDeliveryData(),
			'delivery_cost' => $this->getDeliveryCost(),
			'delivery_note' => $this->getDeliveryNote(),
			'products_cost' => $this->getProductsCost(),
			'refresh' => 0,
			'note' => $this->note,
		];
        
        if (!$fields) {
            return $data;
        }
        return array_intersect_key($data, array_combine($fields,$fields));
    }
}