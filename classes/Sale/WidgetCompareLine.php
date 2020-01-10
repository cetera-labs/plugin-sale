<?php
namespace Sale;

class WidgetCompareLine extends \Cetera\Widget\Templateable
{

    protected $_params = array(
        'compare_url' => '/cart/',
	    'template'       => 'default.twig',
    ); 

	public function getCompare()
	{
		return \Sale\Compare::get();
	}
	
}