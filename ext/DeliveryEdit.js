Ext.define('Plugin.sale.DeliveryEdit', {
    extend:'Ext.Window',

    modal: true,
    autoShow: true,
    width: 500,
    minWidth: 400,
    minHeight: 200,
	layout: 'fit',
		
    items: {
		xtype: 'form',		
		itemId: 'form',
		layout: 'anchor',
		defaults: {
			anchor: '100%',
			labelWidth: 200,
			hideEmptyLabel: false
		},
		border: false,
		defaultType: 'textfield',
		bodyPadding: 10,		
		bodyCls: 'x-window-body-default', 

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
				xtype: 'numberfield',
				fieldLabel: _('Сортировка'),
				allowDecimals: false,
				step: 10,
				name: 'tag',
				allowBlank: false
			},			
			{
				fieldLabel: _('Калькулятор стоимости'),
				name: 'calculator',
				itemId: 'calculator',
				xtype: 'combobox',
				displayField: 'name',
				valueField: 'class',
				editable: false,				
				store: {
					autoLoad: true,
					fields: ['class','name','params'],
					proxy: {
						type: 'ajax',
						url: '/plugins/sale/data_delivery_calculators.php',
						reader: {
							type: 'json',
							root: 'rows'
						}
					}					
				}				
			},
			{
				xtype: 'button',
				margin: '0 0 5 205',
				text: _('Настроить'),
				handler: function(btn) {
					var f = btn.up('form');
					var c = f.getComponent('calculator');
					var params = c.getStore().getById( c.getValue() );
					var cp = f.getComponent('calculator_params');
					//console.log(params.data);
					if (!params) return;
					Ext.create('Plugin.sale.DeliveryCalculator',{
						data: params.data,
						values: Ext.JSON.decode( cp.getValue(), true ),
						listeners: {
							'dataReady': function( values ) {
								cp.setValue( Ext.JSON.encode(values) );
							}
						}
					});
				}
			},
			{
				xtype: 'hiddenfield',
				itemId: 'calculator_params',
				name: 'calculator_params'
			}			
		],
		
		buttons: [
			{
				text    : Config.Lang.ok,
				handler : function() {
					var f = this.up('form').getForm();
					if (!f.isValid()) return;
					f.updateRecord();
					if (!f.getRecord().getId()) this.up('window').fireEvent('recordcreated', f.getRecord());
					this.up('window').destroy();
				}
			},{
				text    : Config.Lang.cancel,
				handler : function() {
					this.up('window').destroy();
				}
			}
		]
	},
	
    initComponent: function(){

		this.title = this.record?Config.Lang.edit+'"'+this.record.get('name')+'"':_('Новый способ доставки'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.Delivery');
		this.callParent();
		this.getComponent('form').getForm().loadRecord( this.record );
	}
	  
});