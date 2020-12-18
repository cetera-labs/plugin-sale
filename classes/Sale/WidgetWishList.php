<?php
namespace Sale;

class WidgetWishList extends \Cetera\Widget\Templateable
{
	
	use \Cetera\Widget\Traits\Paginator;
	use \Cetera\Widget\Traits\Catalog;

    protected $_params = array(
		'limit'              => 0,
		'page'               => null,
		'page_param'         => 'page',	
		'paginator'          => false,
		'paginator_template' => false,
		'paginator_url'      => '?{query_string}',
		'template'         => 'default.twig',
    ); 
	
	protected function init()
	{
	    $this->application->addScript('/plugins/sale/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/cms/plugins/sale/js/common.js');

		$wid = $this->getUniqueId();
		if (isset($_REQUEST[$wid]) && $_REQUEST[$wid] == 'clear') {
			WishList::get()->clear();
			if (getenv('HTTP_REFERER')) {
				header('Location: '.getenv('HTTP_REFERER'));
				die();
			}
		}
	}		
	
	/**
	 * Список товаров для показа
	 */ 	
    public function getChildren()
    {	
		$list = WishList::get()->getProducts();
		if ($this->getParam('limit')) $list->setItemCountPerPage($this->getParam('limit')); 
		$list->setCurrentPageNumber( $this->getPage() ); 
		return $list;		
    } 	
	
}