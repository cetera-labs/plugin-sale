<?php
namespace Sale;

class WidgetCartLine extends \Cetera\Widget\Templateable
{

    protected $_params = array(
        'cart_url' => '/cart/',
	    'template'       => 'default.twig',
    ); 

	public function getCart()
	{
		return \Sale\Cart::get();
	}
	
}