Ext.define('Plugin.sale.OrderEditProps', {
	
    extend:'Ext.Window',
	requires: [
		'Cetera.field.User',
		'Plugin.sale.model.Payment',
		'Plugin.sale.model.Delivery'
	],

    modal: true,
    autoShow: true,
    width: '40%',
    minWidth: 400,
    minHeight: 300,
		
    initComponent: function() {

		this.title = _('Заказ №')+this.record.getId() + ' '+ _('от') +' ' + this.record.get('date'); 
		
		var d = this.record.getData();
		//console.log(d);
		
		this.callParent();	
		
		var form = this.getComponent('form');
		form.getForm().loadRecord( this.record );
		form.getComponent('props').getStore().loadData(d.props);
		
    },
	
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
				xtype: 'userfield',
				name: 'user_id',
				allowBlank: false,
				fieldLabel: _('Покупатель')
			},
			{
				xtype: 'combobox',
				name: 'payment_id',
				allowBlank: false,
				fieldLabel: _('Оплата'),
				displayField: 'name',
				valueField: 'id',
				store: {
					model: 'Plugin.sale.model.Payment',
					sorters: [{property: "tag", direction: "ASC"}],
					remoteSort: false,
					autoLoad: true,
					autoSync: true			
				}					
			},
			{
				xtype: 'combobox',
				name: 'delivery_id',
				allowBlank: false,
				fieldLabel: _('Доставка'),
				displayField: 'name',
				valueField: 'id',
				store: {
					model: 'Plugin.sale.model.Delivery',
					sorters: [{property: "tag", direction: "ASC"}],
					remoteSort: false,
					autoLoad: true,
					autoSync: true			
				}					
			},
			{
				xtype: 'numberfield',
				name: 'delivery_cost',
				allowBlank: false,
				minValue: 0,
				fieldLabel: _('Стоимость доставки')				
			},
			{
				xtype: 'textarea',
				name: 'delivery_note',
				fieldLabel: _('Комментарий к доставке')	
			},
			{
				xtype: 'props-grid',
				itemId: 'props',
				store: {
					xtype: 'array',
					fields: ['name', 'value']
				},
				height: 300
			}			
		],
		
		buttons: [
			{
				text: _('OK'),
				handler: function(button) {
					var form = this.up('form');
					var f = form.getForm();
					if (!f.isValid()) return;

					var props = [];
					Ext.Array.each(form.getComponent('props').getStore().getRange(), function(item) {
						var d = item.raw;
						d.value = item.get('value');
						Ext.Array.push(props, d);
					}, this);
					f.updateRecord();
					f.getRecord().set('props', props);
					this.up('window').setLoading(true);
					this.up('window').store.sync({
						scope: this,
						callback: function() {
							var w = this.up('window');
							w.setLoading(false);
							w.fireEvent('order_updated', this.up('form').getForm().getRecord());
							w.destroy();
						}
					});					
				}
			},{
				text: _('Отмена'),
				handler: function(button) {
					button.up('window').close();
				}
			}
		]		
	}
	
});

Ext.define('Plugin.sale.PropsGrid', {
    extend: 'Ext.grid.Panel',
    xtype: 'props-grid',
	
    columns: [
        {
			text: _("Свойство"), width: 200, dataIndex: 'name'
		},
        {
			text: _("Значение"), 
			flex: 1, 
			dataIndex: 'value',
			editor: {
				allowBlank: true
            }
		}
    ],	
	
    initComponent: function() {
        this.cellEditing = new Ext.grid.plugin.CellEditing({
            clicksToEdit: 1
        });
		
		Ext.apply(this, {
            plugins: [this.cellEditing]
		});
		
		this.callParent();
    }	
});