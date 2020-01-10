<?php
namespace Sale;

class WidgetCompare extends \Cetera\Widget\Templateable
{
	private $_fields = null;
	
	protected function initParams()
	{
		$this->_params = [
			'template'       => 'default.twig',
			'limit'          => 0,
			'fields'		 => null,
			'pages'			 => null,
		];		
	}		

	protected function init()
	{
	    $this->application->addScript('/plugins/sale/js/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/plugins/sale/js/common.js');
	}	
	
	public function getCompare()
	{
		return \Sale\Compare::get( $this->getParam('limit') );
	}
	
	public function isDiffOnly()
	{
		return (isset($_GET['diff']))?true:false;
	}	
	
	public function getFields()
	{
		if ($this->_fields === null) {
			$this->_fields = [];
			
			$od = Product::getObjectDefinition();
			
			if ($this->getParam('fields')) {
				$fields = explode(',', $this->getParam('fields'));
			}
			else {
				if ($this->getParam('pages')) {
					$pages = explode(',', $this->getParam('pages'));
				}
				else {
					$pages = null;
				}
				$fields = [];
				foreach ($od->getFields() as $f) {
					if ($pages && !in_array($f['page'], $pages)) {
						continue;
					}
					$fields[] = $f->name;
				}
			}
			
			$sections = [];
			foreach ($this->getCompare()->getProducts() as $p) {
				$sections[ $p->catalog->id ] = $od->getFields( $p->catalog );
			}
			
			foreach ($fields as $f) {
				$f = trim($f);
				
				try {
					$fld = $od->getField( $f );
					
					if (count($sections)) {
						$visible = false;
						foreach($sections as $s) {
							if ($s[$f]->isVisible()) {
								$visible = true;
								break;
							}
						}
						if (!$visible) continue;
					}
					if ( $this->isDiffOnly() && !$this->getCompare()->isValuesDiffer($fld) ) {
						continue;
					}					
					$this->_fields[] = $fld;
				} catch (\Exception $e) {
					continue;
				}
			}
			
		}
		return $this->_fields;
	}

	public function fieldValue($product, $field)
	{
		return $this->getCompare()->fieldValue($product, $field);
	}	
	
    public function getChildren() {
		return $this->getCompare()->getProducts();
	}		
	
}