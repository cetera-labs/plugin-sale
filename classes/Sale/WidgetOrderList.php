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
	    $this->application->addScript('/plugins/sale/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/cms/plugins/sale/js/common.js');		
	}	

public function getOrders()
{
    $user = $this->application->getUser();
    if (!$user) return null;        

    $orders = $user->getOrders()->orderBy($this->getParam('order'), $this->getParam('sort'));        
    if ($this->getParam('limit')) $orders->setItemCountPerPage($this->getParam('limit')); 
    if ($this->getParam('where')) $orders->where($this->getParam('where'));         
    $orders->setCurrentPageNumber($this->getPage());

    $db = \Cetera\DbConnection::getDbConnection();

    $orderIds = [];
    foreach ($orders as $o) {
        $orderIds[] = $o->id;
    }
    if (!empty($orderIds)) {
        $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
        $rows = $db->fetchAll("
            SELECT order_id, product_id
            FROM sale_order_products
            WHERE order_id IN ($placeholders)
            ORDER BY order_id, id
        ", $orderIds);
        $productIdsByOrder = [];
        foreach ($rows as $row) {
            $productIdsByOrder[$row['order_id']][] = $row['product_id'];
        }
        foreach ($orders as $order) {
            $order->product_ids = $productIdsByOrder[$order->id] ?? [];
        }
    } else {
        foreach ($orders as $order) {
            $order->product_ids = [];
        }
    }

    return $orders;
}
	
}