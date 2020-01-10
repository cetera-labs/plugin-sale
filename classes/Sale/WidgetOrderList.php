<?php
namespace Sale;

class WidgetOrderList extends \Cetera\Widget\Templateable
{
    use \Cetera\Widget\Traits\Paginator;

    protected $_params = array(
        'where'              => null,
        'limit'              => 10,
        'page'               => null,
        'page_param'         => 'page',
        'order'              => 'date',
        'sort'               => 'DESC',
		'order_detail_url' => '/personal/order?id={id}',
		'template'         => 'default.twig',
    ); 
	
	public function init()
	{
	    $this->application->addScript('/plugins/sale/js/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/plugins/sale/js/common.js');		
	}	

	public function getOrders()
	{
		$user = $this->application->getUser();
		if (!$user) return null;        
		$orders = $user->getOrders()->orderBy($this->getParam('order'), $this->getParam('sort'));        
		if ($this->getParam('limit')) $orders->setItemCountPerPage($this->getParam('limit')); 
		if ($this->getParam('where')) $orders->where($this->getParam('where'));         
        $orders->setCurrentPageNumber( $this->getPage() );
        return $orders;
	}
	
}