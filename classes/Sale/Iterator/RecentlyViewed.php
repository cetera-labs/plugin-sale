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
		
    public function __construct($exclude = null) {
		if (isset($_SESSION['sale_recently_viewed'])) {
			$list = [];
			foreach($_SESSION['sale_recently_viewed'] as $pid) {
				if ($exclude == $pid) continue;
				try {
					$list[] = \Sale\Product::getById($pid);
				}
				catch (\Exception $e) {
					continue;
				}
			}
			return parent::__construct($list);
		}
		else {
			return parent::__construct();
		}
    }   	
	
	public function where() {
		return $this;
	}
	
	public function exclude($pid) {
		return $this;
	}	
}
