Ext.define('Plugin.sale.DiscountsAndCoupons', {
	
	extend:'Ext.Panel',
	requires: ['Plugin.sale.Discounts', 'Plugin.sale.Coupons'],
	
	border: false,	
	bodyCls: 'x-window-body-default', 
	
	layout: {
		type: 'vbox',
		padding: 3,
		align : 'stretch'
	},

	items: [
		{ 
			xtype: 'sale.discounts',
			padding: 3,
			flex: 1
		},
		{ 
			xtype: 'sale.coupons',
			padding: 3,
			flex: 1
		}		
	]
	
});