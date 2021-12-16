<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Sale\Iterator; 
 
class Order extends \Cetera\Iterator\DbObject {
		
	use \Cetera\Traits\Extendable;

    public function __construct()
    { 
        parent::__construct();
        $this->query->select('sale_orders.*')->from('sale_orders');
		$this->query->leftJoin('sale_orders', 'sale_order_props_value', 'props', 'sale_orders.id = props.order_id')->add('groupBy', 'sale_orders.id', true);
        
    } 
    	
	protected function fetchObject($row)
	{
		$order = \Sale\Order::create();
		$order->setData($row);
		return $order;
	}   

    public function search($query)
    { 		
		$this->where('sale_orders.id=:query_int OR props.value like :query');
		$this->setParameter('query_int', (int)$query);		
		$this->setParameter('query', '%'.$query.'%');	
        return $this;
    }	
	
	public function filter($values) {
		if (isset($values['date_from'])) $this->where( 'date >= STR_TO_DATE(:date_from,"%Y-%m-%d")' )->setParameter('date_from',$values['date_from']);
		if (isset($values['date_to'])) $this->where( 'date <= STR_TO_DATE(:date_to,"%Y-%m-%d")' )->setParameter('date_to',$values['date_to']);
		if (isset($values['status'])) $this->where( 'status = :status' )->setParameter('status', (int)$values['status']);	
		return $this;		
	}

}
