Ext.define('Plugin.sale.Setup', {
	
	extend:'Ext.Panel',
	requires: ['Plugin.sale.Filter','Plugin.sale.PersonType','Plugin.sale.Currency'],
	
	border: false,	
	bodyCls: 'x-window-body-default', 
	
	layout: {
		type: 'vbox',
		padding: 3,
		align : 'stretch'
	},

	items: [
		{
			itemId: 'setup_form',
			title: _('Настройки магазина'),
			xtype: 'form',
			defaultType: 'textfield',
			buttonAlign: 'left',
			bodyCls: 'x-window-body-default', 
			bodyPadding: 5,
			border: false,
			defaults: {
				anchor: '50%',
				labelWidth: 300,
				hideEmptyLabel: false
			},			
			items: [
				{
					fieldLabel: _('Email отдела продаж'),
					name: 'sale_email'
				},
				{
					fieldLabel: _('Точность цены (десятичных знаков)'),
					name: 'price_decimals',
					xtype: 'numberfield',
					value: 2,
					maxValue: 10,
					minValue: 0					
				},
				{
					boxLabel: _('учет остатков товаров'),
					name: 'use_quantity',
					xtype: 'checkbox',
					inputValue: 1,
					uncheckedValue: 0
				}				
			],
			buttons: [
				{
					text: _('Сохранить'),
					handler: function() {
						this.up('form').setLoading(true);
						Ext.Ajax.request({
							url: '/plugins/sale/data_setup.php',
							method: 'POST',
							params: this.up('form').getForm().getValues(),
							success: function(response, opts) {
								this.up('form').setLoading(false);
							},
							scope: this
						});						
						
					}
				}
			]
		},
		{ 
			title: _('Настройки фильтра'),
			xtype: 'sale.filter',
			padding: 3,
			flex: 1
		},
		{
			border: false,	
			bodyCls: 'x-window-body-default', 			
			flex: 1,			
			layout: {
				type: 'hbox',
				align : 'stretch'
			},	
			items: [
				{ 
					xtype: 'sale.person_type',
					padding: 3,
					flex: 1
				},
				{ 
					xtype: 'sale.currency',
					padding: 3,
					flex: 1
				}				
			]
		}		
	],
	
	afterRender: function(){
		this.getComponent('setup_form').setLoading(true);
		Ext.Ajax.request({
			url: '/plugins/sale/data_setup.php',
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				this.getComponent('setup_form').getForm().setValues(obj);
				this.getComponent('setup_form').setLoading(false);
			},
			scope: this
		});
	
		this.callParent();
	}
	
});