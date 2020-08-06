<?php
namespace Sale;

class WidgetGoodsList extends \Cetera\Widget\Templateable
{

	use \Cetera\Widget\Traits\Catalog;
	use \Cetera\Widget\Traits\Paginator;
	
	protected function initParams()
	{
		$this->_params = array(
			'name'               => '',
			'catalog'            => null,
			'iterator'			 => null,
			'where'              => null,
			'limit'              => 10,
			'page'               => null,
			'page_param'         => 'page',
			'order'              => 'dat',
			'sort'               => 'DESC',
			'paginator'          => false,
			'paginator_template' => false,
			'paginator_url'      => '?{query_string}',
			'css_row'            => 'small-up-1 medium-up-2 large-up-4',
			'template'           => 'default.twig',
		);
	}	

	protected function init()
	{
	        $this->application->addScript('/plugins/sale/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/cms/plugins/sale/js/common.js');				
	}		
	
	/**
	 * Список товаров для показа
	 */ 	
    public function getChildren()
    {
		if (!$this->_children)
		{
			if ($this->getParam('iterator'))
			{
				$this->_children = $this->getParam('iterator');
			}
			else
			{
				$cat = $this->getCatalog( false );
				if ($cat)
				{
					$this->_children = $cat->getMaterials()->subfolders();
				}
				else
				{
					$this->_children = \Sale\Product::getObjectDefinition()->getMaterials();
				}
				
				$this->_children->orderBy($this->getParam('order'), $this->getParam('sort'));				
			}
			if ($this->getParam('limit')) $this->_children->setItemCountPerPage($this->getParam('limit')); 
			if ($this->getParam('where')) $this->_children->where($this->getParam('where')); 
			//$this->_children->where('price>0');
			$this->_children->setCurrentPageNumber( $this->getPage() ); 			
		}
		return $this->_children;
    } 	
	
}