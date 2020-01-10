<?php
ini_set('display_errors',1);

$application->connectDb();
$application->initSession();
$application->initPlugins();
$t = $application->getTranslator();

$order = \Sale\Order::getById( (int)$_REQUEST['order'] );

if (!$order->canBePaid()) {
	die($t->_('В настоящее время заказ не может быть оплачен'));
}

try {
    $order->getPaymentGateway()->pay($_REQUEST['return']);
}
catch (\Exception $e) {
    print $e->getMessage();
}