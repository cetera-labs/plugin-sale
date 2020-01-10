<?php
namespace Sale\DeliveryCalculator;

class Fixed extends CalculatorAbstract {
	
	public static function getInfo()
	{
		$t = \Cetera\Application::getInstance()->getTranslator();
		
		return array(
			'name'   => $t->_('Фиксированная стоимость'),
			'params' => array(
				array(
					'name'       => 'cost',
					'xtype'      => 'numberfield',
					'fieldLabel' => $t->_('Стоимость доставки'),
					'value'      => 100,
					'step'       => 100,
				),
				array(
					'name'         => 'currency',
					'xtype'        => 'combobox',
					'fieldLabel'   => $t->_('Валюта'),
					'value'        => 'RUB',
					'editable'     => false,
                    'valueField'   => 'code',
                    'displayField' => 'name',
					'store'	       => [
						'model' => "Plugin.sale.model.Currency",
						'sorters' =>  [
							[
								'property' => "sort", 
								'direction' => "ASC"
							]
						],
						'remoteSort' =>  false,
						'autoLoad' =>  true
					],
				)				
			)
		);
	}
		
	public function calculate()
	{
		return (float)$this->params['cost'];
	}	
	
	public function getCurrency()
	{
		try {
			if (!$this->params['currency']) throw new \Exception('Currency is not set');
			return \Sale\Currency::getByCode( $this->params['currency'] );
		}
		catch (\Exception $e) {
			return parent::getCurrency();
		}
	}		
	
}
