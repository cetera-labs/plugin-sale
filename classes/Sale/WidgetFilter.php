<?php
namespace Sale;

class WidgetFilter extends \Cetera\Widget\Templateable
{
	
	use \Cetera\Widget\Traits\Catalog;
	
	public $filter = null;
	
    protected $_params = array(
        'catalog'        => null,
		'action'         => null,
		'css_class'      => 'filter',
		'filter_name'    => 'filter',
	    'template'       => 'default.twig',
    ); 

	protected function init()
	{
	        $this->application->addScript('/plugins/sale/js/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/plugins/sale/js/common.js');
		$this->application->addScript('/plugins/sale/js/jquery-ui.min.js');
		if ( $this->getParam('filter') ) {
			$this->filter = $this->getParam('filter');
		}
		else {
			$this->filter = \Sale\Filter::get( $this->getParam('filter_name'), \Sale\Product::getObjectDefinition(), $this->getCatalog() );
		}
	}	
		
}