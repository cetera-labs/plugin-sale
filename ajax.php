<?php
$application->connectDb();
$application->initSession();
$application->initPlugins();
$application->setFrontOffice();

if (isset($_REQUEST['action']))
	$action = $_REQUEST['action'];

if ($action == 'city_lookup')
{
	$data = $application->getDbConnection()->fetchAll('SELECT * FROM sale_places WHERE searchString like ? limit 15',array($_REQUEST['term'].'%'));
	$res = array();
	foreach($data as $d) $res[] = array(
		'value' => $d['name'],
		'label' => $d['name'],
	);
	
	print json_encode($res);
	die();
}

if ($action == 'add_to_cart')
{
	$cart = \Sale\Cart::get();
	$cart->addProduct( 
		(int)$_REQUEST['id'], 
		(int)$_REQUEST['quantity'], 
		isset($_REQUEST['offer_id'])?(int)$_REQUEST['offer_id']:null,
		isset($_REQUEST['options'])?$_REQUEST['options']:null
	);
	print json_encode(array(
		'count' => $cart->getProductsCount(),
		'total' => $cart->getTotal(),
		'total_display' => $cart->getTotal(true),
	));	
	die();
}

if ($action == 'cart_clear')
{
	$cart = \Sale\Cart::get();
	$cart->clear();
	print json_encode(array(
		'count' => $cart->getProductsCount(),
		'total' => $cart->getTotal(),
		'total_display' => $cart->getTotal(true),
	));		
	die();
}

if ($action == 'set_cart_quantity')
{
	$cart = \Sale\Cart::get();
	
	$prod = $cart->setProduct( null, (int)$_GET['quantity'], null, null, (int)$_GET['id'] );

	$data = array(
		'count' => $cart->getProductsCount(),
		'sum'   => $cart->getCurrency()->format( $prod->discountPrice * (int)$_GET['quantity'], $prod->currency),
		'total_sum' => $cart->getDisplayTotal(),
		'total' => $cart->getTotal(),
		'total_display' => $cart->getTotal(true),
	);
	print json_encode($data);	
	die();
}

if ($action == 'set_cart_option')
{
	$cart = \Sale\Cart::get();	
	$prod = $cart->setProductOption( (int)$_GET['id'], $_GET['option_name'], $_GET['option_value'] );
	$data = array(
		'success' => true,
	);
	print json_encode($data);	
	die();
}

if ($action == 'delivery_calculate')
{
	$order = \Sale\Order::make( \Sale\Cart::get() );
	$_POST['delivery_method'] = (int)$_GET['delivery_method'];
	$order->setParams($_POST);
	print json_encode(array(
		'total'         => $order->getTotal(true),
		'delivery_cost' => $order->getDeliveryCost(true),	
		'html'          => $order->getDelivery()->getHtml(),
		'data' 	        => $order->getDelivery()->getCalculator()->getData(),
	));		
	die();
}

if ($action == 'get_payment_methods')
{
	$order = \Sale\Order::make( \Sale\Cart::get() );
	$order->setParams($_POST);
	
	$delivery_cost = null;
	$total_cost = null;
	try {
		$delivery_cost = $order->getDeliveryCost(true);
		if ($delivery_cost !== null) $total_cost = $order->getTotal(true);
	}
	catch (\Exception $e) {
		$delivery_cost = $e->getMessage();
		$total_cost = null;
	}
	
	$data = array(
		'payment'       => $order->getPaymentMethods(),
		'delivery'      => $order->getDeliveryMethods(),
		'total'         => $total_cost,
		'delivery_cost' => $delivery_cost,
		'delivery_id'   => $order->getDeliveryId(),
	);	
	print json_encode($data);	
	die();	
}

if ($action == 'order_cancel')
{
	$order = \Sale\Order::getById( (int)$_REQUEST['id'] );
	if (!$application->getUser() || $order->getUser()->id != $application->getUser()->id)
		throw new \Exception('Доступ запрещен');
	$order->setStatus(\Sale\Order::STATUS_CANCELLED)->save();
	die( $order->getStatusText() );	
}

if ($action == 'wishlist_add') {
	$wish = \Sale\WishList::get();
	$wish->addProduct((int)$_REQUEST['id']);
	print json_encode(array(
		'count' => $wish->getProductsCount()
	));
	die();	
}

if ($action == 'wishlist_remove') {
	$wish = \Sale\WishList::get();
	$wish->removeProduct((int)$_REQUEST['id']);
	print json_encode(array(
		'count' => $wish->getProductsCount()
	));	
	die();	
}

if ($action == 'compare_add') {
	$cmp = \Sale\Compare::get();
	$cmp->addProduct($_REQUEST['id']);
	print json_encode(array(
		'id' => $_REQUEST['id'],
		'count' => $cmp->getProductsCount()
	));
	die();	
}

if ($action == 'compare_remove') {
	$cmp = \Sale\Compare::get();
	$cmp->removeProduct($_REQUEST['id']);
	print json_encode(array(
		'id' => $_REQUEST['id'],
		'count' => $cmp->getProductsCount()
	));
	die();	
}

if ($action == 'compare_clear') {
	\Sale\Compare::get()->clear();
	print json_encode(array(
		'success' => true
	));
	die();	
}