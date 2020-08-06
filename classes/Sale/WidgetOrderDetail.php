<?php
namespace Sale;

class WidgetOrderDetail extends \Cetera\Widget\Templateable
{

    protected $_params = array(
		'order'    => null,
		'template' => 'default.twig',
    ); 

	public function init()
	{
	    $this->application->addScript('/plugins/sale/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/cms/plugins/sale/js/common.js');		
	}
	
	public function getOrder()
	{
		$user = $this->application->getUser();
		if (!$user) return null;
		try {
			$order = \Sale\Order::getById( (int)$this->getParam('order') );
		}
		catch (\Exception $e) {
			return null;
		}
		if ($user->id != $order->getUser()->id) return null;
		return $order;
	}
	
}