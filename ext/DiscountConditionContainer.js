Ext.create('Ext.data.Store', {
    storeId: 'saleDiscountConditionFieldsStore',
	fields: [
		{ name:'id', type: 'int'},
		'alias', 'name',
		{ name:'type', type: 'int'}
	],
	proxy: {
		 type: 'ajax',
		 url: '/plugins/sale/data_discount_conditions.php',
		 reader: {
			 type: 'json',
			 root: 'rows'
		 }
	},
	autoLoad: true	
});

Ext.define('Plugin.sale.DiscountConditionContainer', {
    extend: 'Ext.FormPanel',
    
    closable: true,
    
    margin: '5 5 0 5',
    bodyCls: 'x-window-body-default',
    bodyPadding: 5,
	layout: 'hbox',
	defaults: {
		hideEmptyLabel: true
	},
	
	title: 'Условие',
	
	initComponent: function() {
		
		this.comboCondition = Ext.create('Ext.form.field.ComboBox',{
			flex: 2,
			name: 'field',
			editable: false,
			displayField: 'name',
			valueField: 'alias',			
			store: 'saleDiscountConditionFieldsStore'					
		});
		
		this.comboRelation = Ext.create('Ext.form.field.ComboBox',{
			flex: 1,
			name: 'condition',
			editable: false,
			store: [
				['eq',_('равно')],
				['neq',_('не равно')],
				['like',_('содержит')],
				['not_like',_('не содержит')],
				['gt',_('больше или равно')],
				['lt',_('меньше')]
			],
			value: 'eq'
		});	

		this.conditionValue = Ext.create('Ext.form.field.Text',{
			flex: 2,
			name: 'value',
			allowBlank: false
		});			

		Ext.apply(this, {
			items: [
				this.comboCondition, {
					xtype: 'splitter'
				},
				this.comboRelation, {
					xtype: 'splitter'
				}, 
				this.conditionValue
			]
		});
	
		this.callParent();
	
	}
	
});