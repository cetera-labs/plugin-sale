<?php
namespace Sale;

class WidgetGoodsItem extends \Cetera\Widget\Material
{
    
	private $tabs = null;
	private $properties = null;
	
	protected function initParams()
	{
		$this->_params = array(
			'login_url'    => '/login',
			'register_url' => '/register',
			
			'field_picture'  => 'pic',
			'field_pictures' => 'pictures',	
			
			'show_options'  => false,
			'show_tabs' 	=> 'text',
			'show_properties' => 'code',
			'show_comments' => true,
			'share_buttons' => true,
            'show_meta'     => false,
			
			'template'     => 'default.twig',
		);
	}
	
	protected function init()
	{
		parent::init();
		
	        $this->application->addScript('/plugins/sale/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/cms/plugins/sale/js/common.js');		
	
		// формируем массив недавно просмотренных товаров
		$m = $this->getMaterial();
		if ($m)
		{
			if (!isset($_SESSION['sale_recently_viewed']))
			{
				$_SESSION['sale_recently_viewed'] = array();
			}
			array_unshift($_SESSION['sale_recently_viewed'], $m->id);
			$_SESSION['sale_recently_viewed'] = array_unique($_SESSION['sale_recently_viewed']);
			
			if ($this->getParam('show_meta')) {
                $a = $this->application;                
				$a->addHeadString('<meta property="og:type" content="product.item"/>', 'og:type');
			}
		}		
	}	

	public function isPluginComments()
	{
		// Проверяем, доступен ли модуль Комментарии
		$pc = \Cetera\Plugin::find('comments');
		if ($pc) $pc = $pc->isEnabled();
		return $pc;	
	}
	
	public function getRecentlyViewed()
	{
		return new Iterator\RecentlyViewed( $this->getMaterial()->id );
	}
	
	public function getTabs()
	{
		if (!$this->tabs)
		{
			if (!is_array($this->getParam('show_tabs')))
			{
				$this->tabs = $this->getFields( $this->getParam('show_tabs') );
			}
			else
			{
				//print_r( $this->getParam('show_tabs') );
				$this->tabs = array();
				foreach ( $this->getParam('show_tabs') as $i => $t )
				{
					if (isset( $t['page'] ))
					{
						$tab = array(
							'id'    => 'tab_'.$i,
							'name'  => $t['page'],
							'value' => $this->getFieldsPage($t['page'])
						);
						//print_r($tab);
					}
					elseif (isset( $t['field'] ))
					{
						$tab = $this->getField( $t['field'] );
					}
					else
					{
						continue;
					}
					
					if (isset($t['name']))
					{
						$tab['name'] = $t['name'];
					}
					
					$this->tabs[] = $tab;
				}
			}
		}
		return $this->tabs;
	}
	
	public function getProperties()
	{
		if (!$this->properties)
		{
			$this->properties = $this->getFields( $this->getParam('show_properties') );
		}
		return $this->properties;
	}	
	
	private function getFieldsPage($page)
	{
		$m = $this->getMaterial();
		$od = $m->getObjectDefinition();

		$fields = array();
		foreach ( $od->getFields( $m->catalog ) as $field )
		{
			if (!$field['shw']) continue;
			if ($field['page'] != $page) continue;
			$fields[] = $this->getField( $field['name'] );
		}
		return $fields;
	}
	
	private function getFields($list)
	{
		$fields = array();
		$tabs = explode(',', $list );
		foreach ($tabs as $t)
		{
			$t = trim($t);
			if (!$t) continue;
			
			try
			{
				$fields[] = $this->getField( $t );
			}
			catch (\Exception $e)
			{
				continue;
			}
			
		}
		return $fields;
	}
	
	private function getField($t)
	{
		$m = $this->getMaterial();
		$od = $m->getObjectDefinition();
		
		$f = $od->getField($t);
		$value = $m->$t;
		if (is_a($value,'\Cetera\Material'))
		{
			$value = $value->name;
		}
		return array(
			'id'    => $t,
			'name'  => $f['describ'],
			'value' => $value
		);		
	}		
}