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

$payParams = [];
if (isset($_REQUEST['params'])) {
    $payParams = unserialize($_REQUEST['params']);
}

try {
    $order->getPaymentGateway()->pay($_REQUEST['return'], $payParams );
}
catch (\Exception $e) {
    print $e->getMessage();
}