Ext.define('Plugin.sale.PaymentEdit', {
    extend:'Ext.Window',
	requires: ['Plugin.sale.model.Delivery','Plugin.sale.model.PersonType','Cetera.field.File'],

    modal: true,
    autoShow: true,
    width: 500,
    height: 550,
    minWidth: 400,
    minHeight: 600,
	maxHeight: 600,
	layout: 'fit',
		
    items: {
		xtype: 'form',		
		itemId: 'form',
		layout: 'anchor',
		defaults: {
			anchor: '100%',
			labelWidth: 150,
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
				fieldLabel: _('Картинка'),
				name: 'picture',
				xtype: 'fileselectfield'
			},			
			{
				fieldLabel: _('Описание'),
				name: 'note',
				xtype: 'textarea'
			},	
			{
				fieldLabel: _('Платежная система'),
				name: 'gateway',
				itemId: 'gateway',
				xtype: 'combobox',
				displayField: 'name',
				valueField: 'class',
				editable: false,				
				store: {
					autoLoad: true,
					fields: ['class','name','params'],
					proxy: {
						type: 'ajax',
						url: '/plugins/sale/data_payment_gateways.php',
						reader: {
							type: 'json',
							root: 'rows'
						}
					}					
				}				
			},
			{
				xtype: 'button',
				margin: '0 0 5 155',
				text: _('Настроить'),
				handler: function(btn) {
					var f = btn.up('form');
					var c = f.getComponent('gateway');
					var params = c.getStore().getById( c.getValue() );
					var cp = f.getComponent('gateway_params');
					//console.log(params.data);
					if (!params) return;
					Ext.create('Plugin.sale.PaymentGateway',{
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
				itemId: 'gateway_params',
				name: 'gateway_params'
			},			
			{
				name: 'delivery_methods',
				itemId: 'dm',
				xtype: 'hiddenfield'
			},	
			{
				name: 'person_types',
				itemId: 'pt',
				xtype: 'hiddenfield'
			},		
			{
				itemId: 'delivery_methods',
				xtype: 'grid',
				multiSelect: true,
				store: {
					model: 'Plugin.sale.model.Delivery',
					autoLoad: true,		
					listeners: {
						load: {
							fn: function(store) {
								var d = store.window.record.get('delivery_methods').split(',');
								Ext.Array.each(d,function(id){
									if (id) store.grid.getSelectionModel().select([store.getById(parseInt(id))], true);
								},this);
							}
						}
					}					
				},
				hideHeaders: true,
				title: _('Службы доставки, связанные с платежной системой'),
				height: 150,
				columns: [
					{text: "ID",       width: 50, dataIndex: 'id'},	
					{text: "Название", flex: 1, dataIndex: 'name'}
				],
				selModel: {
					mode: 'SIMPLE'
				}
			},		
			{
				itemId: 'person_types',
				xtype: 'grid',
				multiSelect: true,
				store: {
					model: 'Plugin.sale.model.PersonType',
					autoLoad: true,		
					listeners: {
						load: {
							fn: function(store) {
								var d = store.window.record.get('person_types').split(',');
								Ext.Array.each(d,function(id){
									if (id) store.grid.getSelectionModel().select([store.getById(parseInt(id))], true);
								},this);
							}
						}
					}					
				},
				hideHeaders: true,
				title: _('Типы плательщиков, связанные с платежной системой'),
				height: 150,
				columns: [
					{text: "ID",       width: 50, dataIndex: 'id'},	
					{text: _("Название"), flex: 1, dataIndex: 'name'}
				],
				selModel: {
					mode: 'SIMPLE'
				}
			}		
		],
		
		buttons: [
			{
				text    : _('OK'),
				handler : function() {
					var f = this.up('form');
					var r = f.getRecord();
					
					if (!f.isValid()) return;					
					f.getForm().updateRecord();

					var d = '';
					Ext.Array.each(f.getComponent('delivery_methods').getSelectionModel().getSelection(),function(r){
						d += ','+r.getId();
					});
					f.getComponent('dm').setValue(d);
					
					var d = '';
					Ext.Array.each(f.getComponent('person_types').getSelectionModel().getSelection(),function(r){
						d += ','+r.getId();
					});
					f.getComponent('pt').setValue(d);
					
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
	
    initComponent: function(){

		this.title = this.record?_('Изменить')+' "'+this.record.get('name')+'"':_('Новая платежная система'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.Payment');

		this.callParent();
		var f = this.getComponent('form');
		f.getForm().loadRecord( this.record );
		
		var dm = f.getComponent('delivery_methods');
		dm.getStore().grid = dm;
		dm.getStore().window = this;
		
		var pt = f.getComponent('person_types');
		pt.getStore().grid = pt;
		pt.getStore().window = this;		
	}
	  
});