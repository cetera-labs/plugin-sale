Ext.define('Plugin.sale.CurrencyStore', {

	requires: 'Plugin.sale.model.Currency',
	model: 'Plugin.sale.model.Currency',	
	extends: 'Ext.data.Store',
	autoLoad: true
		  
});