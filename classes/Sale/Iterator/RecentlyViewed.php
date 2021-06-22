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
 
class RecentlyViewed extends \Cetera\Iterator\DynamicObject {
		
    protected $empty;
    
    public function __construct($exclude = null, $max_length = 10) {
        parent::__construct( \Sale\Product::getObjectDefinition() );

		if (isset($_SESSION['sale_recently_viewed']) && is_array( $_SESSION['sale_recently_viewed'] )) {
            while (($i = array_search($exclude, $_SESSION['sale_recently_viewed'])) !== false) {
                unset($_SESSION['sale_recently_viewed'][$i]);
            }
			$this->query->where('main.id IN ('.implode( ',', array_slice($_SESSION['sale_recently_viewed'],0,$max_length) ).')');
            $this->empty = false;
		}
        else {
            $this->empty = true;
        } 
    }

    public function fetchElements()
    {
        if ($this->empty) return [];
        return parent::fetchElements();
    }

    public function getCountAll()
    {
        if ($this->empty) return 0;
        return parent::getCountAll();
    }        
	
	public function exclude($pid) {
		return $this;
	}	
}
