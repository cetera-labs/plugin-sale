<?php
define('GROUP_SALE', -106);
define('PSEUDO_FIELD_CURRENCY', 1200);
define('EDITOR_TEXT_CURRENCY', 200);

$t = $this->getTranslator();
$t->addTranslation(__DIR__.'/lang');

$this->addUserGroup(array(
    'id'      => GROUP_SALE,
    'name'    => $t->_('Управление магазином'),
    'describ' => '',
));

try {
	$od = \Sale\Product::getObjectDefinition();
	$od->registerClass( $od->id , '\Sale\Product' );

	$od = \Sale\Offer::getObjectDefinition();
	$od->registerClass( $od->id , '\Sale\Offer' );
}
catch (\Exception $e) {}

\Cetera\User::addPlugin( '\Sale\User' );

\Sale\Discount::addCondition( '\Sale\Discount\ConditionCartCount' );

$this->registerWidget(array(
    'name'     => 'Sale.Goods.List',
    'class'    => '\\Sale\\WidgetGoodsList',
	'describ'  => $t->_('Список товаров'),
	'icon'     => '/cms/plugins/sale/images/icon_list.png',
	'ui'       => 'Plugin.sale.widget.GoodsList',	
));

$this->registerWidget(array(
    'name'          => 'Sale.Catalog',
    'class'         => '\\Sale\\WidgetCatalog',
    'icon'          => '/cms/plugins/sale/images/icon_goods.png',
    'describ'       => $t->_('Каталог товаров'),
    'ui'            => 'Plugin.sale.widget.Catalog',	
));

$this->registerWidget(array(
    'name'          => 'Sale.Goods.Item',
    'class'         => '\\Sale\\WidgetGoodsItem',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Cart.Line',
    'class'         => '\\Sale\\WidgetCartLine',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Cart',
    'class'         => '\\Sale\\WidgetCart',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Order',
    'class'         => '\\Sale\\WidgetOrder',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Order.List',
    'class'         => '\\Sale\\WidgetOrderList',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Order.Detail',
    'class'         => '\\Sale\\WidgetOrderDetail',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Filter',
    'class'         => '\\Sale\\WidgetFilter',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Wish.List',
    'class'         => '\\Sale\\WidgetWishList',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Compare.Line',
    'class'         => '\\Sale\\WidgetCompareLine',
    'not_placeable' => true
));

$this->registerWidget(array(
    'name'          => 'Sale.Compare',
    'class'         => '\\Sale\\WidgetCompare',
    'not_placeable' => true
));

$params = array(
	'sale.email'   => $t->_('E-mail отдела продаж'),
	
	'order.id'     => $t->_('Номер заказа'),
	'order.email'  => $t->_('E-mail покупателя'),
	'order.name'   => $t->_('Имя покупателя'),
	'order.phone'  => $t->_('Телефон покупателя'),
	'order.date'   => $t->_('Дата заказа'),
	'order.total'  => $t->_('Стоимость заказа'),
	'order.productsTable' => $t->_('Список товаров (HTML)'),
	'order.statusText'    => $t->_('Статус заказа'),
	'order.paymentData.name' => $t->_('Способ оплаты'),
	'order.deliveryData.name' => $t->_('Способ доставки'),
	'order.getProperty(\'property\')' => $t->_('Параметр заказа'),
	
	'server.fullUrl' => $t->_('Адрес сайта'),
	'server.name'    => $t->_('Имя сайта')	
);

\Sale\Delivery::addCalculator('\Sale\DeliveryCalculator\Fixed');

if ($this->getBo()) {

    $this->getBo()->registerEvent('SALE_NEW_ORDER', $t->_('Новый заказ в магазине'), $params);
    $this->getBo()->registerEvent('SALE_ORDER_CHECKED', $t->_('Заказ проверен'), $params);
    $this->getBo()->registerEvent('SALE_ORDER_CANCEL', $t->_('Отмена заказа в магазине'), $params);
    $this->getBo()->registerEvent('SALE_ORDER_PAID', $t->_('Заказ оплачен'), $params);
	$this->getBo()->registerEvent('SALE_ORDER_STATUS_CHANGED', $t->_('Статус заказа изменился'), $params);
	
    $this->getBo()->addEditor(array(
         'id'    => EDITOR_TEXT_CURRENCY,
         'alias' => 'editor_text_currency',
         'name'  => $t->_('Редактор валют')
    ));

    $this->getBo()->addPseudoField(array(
         'id'       => PSEUDO_FIELD_CURRENCY,
         'original' => FIELD_TEXT,
         'len'      => 3,
         'name'     => $t->_('Валюта')
    ));
    
    $this->getBo()->addFieldEditor(PSEUDO_FIELD_CURRENCY, EDITOR_TEXT_CURRENCY);

	include_once( __DIR__.'/editor_text_currency.php' );   	
	
	if ( $this->getUser() && $this->getUser()->hasRight(GROUP_SALE) ) {
		$this->getBo()->addModule(array(
			'id'       => 'sale',
			'position' => MENU_SITE,
			'name' 	   => $t->_('Магазин'),
			'icon'     => '/cms/plugins/sale/images/icon.png',
            'iconCls'  => 'x-fas fa-store',
			'class'    => 'Plugin.sale.Setup',
			'items'  => array(
				array(
                    'id'    => 'orders',
					'name'  => $t->_('Заказы'),
					'icon'  => '/cms/plugins/sale/images/icon_orders.png',
                    'iconCls'=> 'x-fas fa-receipt',
					'class' => 'Plugin.sale.Orders'
				),            	
				array(
                    'id'    => 'payment',
					'name'  => $t->_('Платежные системы'),
					'icon'  => '/cms/plugins/sale/images/money.png',
                    'iconCls'=> 'x-fas fa-credit-card',
					'class' => 'Plugin.sale.Payment'
				),	
				array(
                    'id'    => 'delivery',
					'name'  => $t->_('Способы доставки'),
					'icon'  => '/cms/plugins/sale/images/delivery-icon.png',
                    'iconCls'=> 'x-fas fa-truck',
					'class' => 'Plugin.sale.Delivery'
				),
				array(
                    'id'    => 'opder-props',
					'name'  => $t->_('Свойства заказов'),
					'icon'  => '/cms/plugins/sale/images/icon_orders_props.png',
                    'iconCls'=> 'x-fas fa-asterisk',
					'class' => 'Plugin.sale.OrderPropsSetup'
				),
				array(
                    'id'    => 'goods',
					'name'  => $t->_('Товары'),
					'icon'  => '/cms/plugins/sale/images/icon_goods.png',
                    'iconCls'=> 'x-fas fa-shopping-bag',
					'class' => 'Plugin.sale.Goods'
				),
				array(
                    'id'    => 'discount',
					'name'  => $t->_('Скидки'),
					'icon'  => '/cms/plugins/sale/images/icon_discount.png',
                    'iconCls'=> 'x-fas fa-percentage',
					'class' => 'Plugin.sale.DiscountsAndCoupons'
				),	                
			) 
		));

	}
}
else {
	$this->getTwig()->addGlobal('recently_viewed',  new \Sale\Iterator\RecentlyViewed() );
    $u = $this->getUser();
	if ($u && $u->isAdmin()) {
        $this->addScript('/cms/plugins/sale/js/admin-panel.js');
    }    
}

\Cetera\Event::attach('CORE_MATERIAL_COPY', function($event, $data){
	
	if ($data['src']->objectDefinition->id != \Sale\Product::getObjectDefinition()->id) return;
	if (!$data['src']->hasOffers()) return;
	
	foreach ($data['src']->getOffers() as $o) {
		$new_o = $o->copy(-1);
		$offer = \Sale\Offer::getById($new_o);
		$offer->fields['product'] = $data['dst']->id;
		$offer->save();
	}
	
});

\Cetera\Event::attach(EVENT_CORE_MATH_DELETE, function($event, $data){
	
	if ($data['material']->objectDefinition->id != \Sale\Product::getObjectDefinition()->id) return;
	if (!$data['material']->hasOffers()) return;
	
	foreach ($data['material']->getOffers() as $o) {
		$o->delete();
	}
	
});

$this->getRouter()->addRoute('api_sale',
    \Zend\Router\Http\Segment::factory([
        'route' => '/api/sale[/:controller][/:action][/:id]',
        'constraints' => [
            'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
            'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
            'id'         => '[a-zA-Z0-9_-]+',
        ],
        'defaults' => [
            '__NAMESPACE__' => '\Sale\Api',
            'action'        => 'default',
            'controller'    => '\Sale\Api\IndexController',
        ],
    ])
);