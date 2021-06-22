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
 
class RecentlyViewed extends \Cetera\Iterator\Base {
    
    protected $products = null;
    
    public function __construct($exclude = null, $max_length = 10) {

        $list = [];
		if (isset($_SESSION['sale_recently_viewed']) && is_array( $_SESSION['sale_recently_viewed'] )) {
            while (($i = array_search($exclude, $_SESSION['sale_recently_viewed'])) !== false) {
                unset($_SESSION['sale_recently_viewed'][$i]);
            }
            if (count($_SESSION['sale_recently_viewed'])) {
                $list = array_slice($_SESSION['sale_recently_viewed'],0,$max_length);
            }
		}
        parent::__construct( $list );
    }

    public function getElements()
    {
        if (!$this->products) {
            $this->products = [];
            if (count($this->elements)) {
                foreach (\Sale\Product::enum()->where('main.id IN ('.implode( ',', $this->elements ).')') as $p) {
                    $this->products[] = $p;
                }
                
                usort($this->products, function($a, $b){
                    $key_a = array_search($a->id, $this->elements);
                    $key_b = array_search($b->id, $this->elements);
                    return ($key_a < $key_b) ? -1 : 1;
                });
            }
        }
        return $this->products;
    }

    public function getCountAll()
    {
        return count($this->elements);
    }        
	
	public function exclude($pid) {
		return $this;
	}	

	public function where() {
		return $this;
	}    
}
