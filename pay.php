<?php
ini_set('display_errors',1);

$application->connectDb();
$application->initSession();
$application->initPlugins();
$t = $application->getTranslator();

$order = \Sale\Order::getById( (int)$_REQUEST['order'] );

if (!$order->canBePaid()) {
    
		$user = \Cetera\Application::getInstance()->getUser();
		// неавторизованный пользователь не может ничего оплачивать
		if (!$user) print '[u]';
		// чужие заказы тоже нельзя оплачивать
		if ($order->user_id != $user->id) print '[o!=u]';
		// без платежного шлюза нельзя оплачивать
		if (!$order->getPaymentGateway()) print '[g]';

    die($t->_('В настоящее время заказ не может быть оплачен'));
    
}

try {
    $order->getPaymentGateway()->pay($_REQUEST['return']);
}
catch (\Exception $e) {
    print $e->getMessage();
}