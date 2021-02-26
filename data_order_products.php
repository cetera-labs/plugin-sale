<?php
include('common_bo.php');
$a = \Cetera\Application::getInstance();

if (isset($_GET['action']))
{
	$data = json_decode(file_get_contents("php://input"), true);
	
	$order = \Sale\Order::getById( $_GET['order'] );
	
	if ($_GET['action'] == 'update') {	
		list($product,$offer) = explode('-', $data['id']);
		$order->setProduct($product,$data['quantity'],$offer,null,$data['price']);
		$order->save();
	}
	
	if ($_GET['action'] == 'destroy') {	
		list($product,$offer) = explode('-', $data['id']);
		$order->removeProduct( $product, $offer );
		$order->save();
	}
	
	if ($_GET['action'] == 'create') {	
		list($product,$offer) = explode('|', $data['add_product']);
		$order->addProduct($product,1,$offer);
		$order->save();
		$data['id'] = (int)$product.'-'.(int)$offer;
	}	
	
	foreach ($order->getProducts() as $p) {
		if ($p['id'] == $data['id']) {
			$data = [$p];
			break;
		}
	}		
	
}
else {
	$data = $order->getProducts();
}

echo json_encode(array(
    'success' => true,
	'rows' => $data
));