Ext.define('Plugin.sale.OrderPropsSetup', {
	
	extend:'Ext.Panel',
	requires: ['Plugin.sale.OrderPropsGroups','Plugin.sale.OrderProps'],
	
	border: false,	
	bodyCls: 'x-window-body-default', 
	
	layout: {
		type: 'vbox',
		padding: 3,
		align : 'stretch'
	},

	items: [
		{ 
			title: _('Свойства заказов'),
			xtype: 'sale.order.props',
			padding: 3,
			flex: 2
		},
		{ 
			title: _('Группы свойств'),
			xtype: 'sale.order.props.groups',
			padding: 3,
			flex: 1
		}		
	]
	
});