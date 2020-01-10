<?php
namespace Sale;

class WidgetCatalog extends \Cetera\Widget\Section
{
	
	public $productObjectDefinition = null;
	public $filter = null;
	public $iterator = null;

	protected function initParams()
	{
		$this->_params = array(
			'product_catalog'  => null,		
			'show_meta'        => true,
			
			'filter'           => false,
			'filter_name'      => 'filter',
			'filter_css_class' => 'filter',
			
			'compare'          => true,
			'compare_url'      => '/compare/',	
			
			'item_field_picture'   => 'pic',
			'item_field_pictures'  => 'pictures',
			'item_show_tabs' 	   => 'text',
			'item_show_properties' => 'code',
			'item_show_comments'   => true,		
			'item_share_buttons'   => true,
			'item_show_options'    => false,
			'item_template'        => 'default.twig',
			
			'list_limit'        => 16,
			'list_paginator_url'=> '{catalog}?{filter}page={page}',
			'list_order' 	    => 'name',
			'list_sort' 		=> 'asc',
			
			'page404_title'     => $this->t->_('Страница не найдена'),
			'page404_template'	=> null,	

			'template'       => 'default.twig',
		);  		
	}		
	
	protected function init()
	{
		
	    $this->application->addScript('/plugins/sale/js/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/plugins/sale/js/common.js');
		$this->application->addScript('/plugins/sale/js/jquery-ui.min.js');
				
		$this->productObjectDefinition = \Sale\Product::getObjectDefinition();
		

		if (!$this->application->getUnparsedUrl())
		{
			
			$s = $this->application->getSession();
			
			if (isset($_GET['sort']))
			{
				list($order, $sort) = explode('|',$_GET['sort']);
				if (strtolower($sort) != 'asc' && strtolower($sort) != 'desc') $sort = 'asc';
				if (!$order) $order = 'name';
				$s->saleCatalogOrder = $order;
				$s->saleCatalogSort = $sort;
			}
			if (isset($s->saleCatalogOrder)) $this->setParam('list_order', $s->saleCatalogOrder);
			if (isset($s->saleCatalogSort)) $this->setParam('list_sort', $this->sort = $s->saleCatalogSort);			
		
			$this->iterator = $this->getCatalog()->getMaterials()->subfolders()->orderBy($this->getParam('list_order'), $this->getParam('list_sort'));
						
			if ($this->getParam('filter'))
			{
				$this->filter = \Sale\Filter::get($this->getParam('filter_name'), $this->productObjectDefinition, $this->getCatalog() );
				$this->filter->apply($this->iterator);
			}
		}
		
	}
	
	public function getListTemplate()
	{
		$s = $this->application->getSession();
		$lt = 'default.twig';
		if (isset($s->saleCatalogView)) $lt = $s->saleCatalogView;
		if (isset($_GET['view']) && $_GET['view']) {
			$lt = $_GET['view'].'.twig';
			$s->saleCatalogView = $lt;
		}
		return $lt;
	}

	public function getListPaginatorUrl()
	{
		$value = $this->getParam('list_paginator_url');
		$filter = '';
		if ($this->filter)
		{
			$filter = $this->filter->getQueryString();
			if ($filter) $filter .= '&';
		}
		$value = str_replace('{filter}', $filter, $value );
		return $value;
		
	}
		
}