<?php
namespace Sale;

class User extends \Cetera\ObjectPlugin {
	
	public function getWishList()
	{
		return WishList::get($this->object);
	}
	
	public function getOrders()
	{
		return Order::enum()->where('user_id='.$this->object->id);
	}	
	
}