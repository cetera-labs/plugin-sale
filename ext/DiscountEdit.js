Ext.define('Plugin.sale.DiscountEdit', {
    extend:'Ext.Window',
	requires: ['Plugin.sale.model.Discount','Plugin.sale.DiscountConditionBlock'],

    modal: true,
    autoShow: true,
    width: 800,
    height: 600,
    minWidth: 600,
    minHeight: 400,
	layout: 'fit',
		
    items: {
		xtype: 'form',		
		itemId: 'form',
		layout: 'fit',
		border: false,
        fieldDefaults: {
            labelWidth: 150,
			hideEmptyLabel: false	
        },		

		items: {
            xtype:'tabpanel',	
            activeTab: 0,
			border: false,
            defaults: {
                bodyPadding: 10,
                layout: 'anchor',
				border: false,
				bodyCls: 'x-window-body-default'					
            },
			items: [
				{
					title: _('Скидка'),
					defaultType: 'textfield',
					defaults: {
						anchor: '100%'				
					},				
					items: [
						{
							fieldLabel: _('Название'),
							name: 'name',
							allowBlank: false
						},
						{
							fieldLabel: _('Активность'),
							name: 'active',
							xtype: 'checkboxfield'
						},		
						{
							fieldLabel: _('Тип скидки'),
							name: 'value_type',
							xtype: 'combobox',
							editable: false,
							store: [
								[0,_('в процентах')],
								[1,_('фиксированная сумма')],
								[2,_('установить цену на товар')]
							],
							allowBlank: false
						},			
						{
							fieldLabel: _('Величина скидки'),
							name: 'value',
							xtype: 'numberfield',
							allowBlank: false
						},	
						{
							fieldLabel: _('Максимальная скидка'),
							name: 'max_discount',
							xtype: 'numberfield',
							allowBlank: false
						},			
						{
							xtype: 'numberfield',
							fieldLabel: _('Приоритет'),
							allowDecimals: false,
							step: 1,
							name: 'priority',
							allowBlank: false
						},
						{
							boxLabel: _('прекратить дальнейшее применение скидок'),
							name: 'last_discount',
							xtype: 'checkboxfield'
						},			
						{
							fieldLabel: _('Описание'),
							name: 'describ',
							xtype: 'textarea'
						}
					
					]
				},
				{
					title: _('Условия'),
					layout: 'fit',
					items: {
						labelWidth: 0,
						xtype: 'sale.discountcondition',
						name: 'conditions'
					}
				}
			]

		},
		
		buttons: [
			{
				text    : _('OK'),
				handler : function() {
					var f = this.up('form');
					var r = f.getRecord();
					
					if (!f.isValid()) return;					
					f.getForm().updateRecord();
					
					if (!r.getId()) this.up('window').fireEvent('recordcreated', r);
					this.up('window').destroy();
				}
			},{
				text    : _('Отмена'),
				handler : function() {
					this.up('window').destroy();
				}
			}
		]
	},
	
    initComponent: function() {

		this.title = this.record?_('Изменить скидку')+' "'+this.record.get('name')+'"':_('Новая скидка'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.Discount');

		this.callParent();
		var f = this.getComponent('form');
		f.getForm().loadRecord( this.record );	
	}
	  
});