Ext.define('Plugin.sale.CouponEdit', {
    extend:'Ext.Window',

	requires: ['Plugin.sale.model.Discount','Plugin.sale.model.Coupon'],
	
    modal: true,
    autoShow: true,
    width: 600,
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
				fieldLabel: _('Купон'),
				xtype: 'fieldcontainer',
				layout: 'hbox',
				items: [
					{
						xtype: 'textfield',
						itemId: 'code',
						name: 'code',
						allowBlank: false,
						flex: 1
					},{
						xtype: 'splitter'
					},{
						xtype: 'button',
						text: _('Генерировать'),
						handler: function(btn) {
							var f = btn.up('fieldcontainer');
							var c = f.getComponent('code');
							c.setValue( f.generateCode(6,'1234567890') + '-' + f.generateCode(3,'QWERTYUIOPASDFGHJKLZXCVBNM') );
						}
					}					
				],
				generateCode: function(length, possible) {
				  var text = "";
				  for ( var i=0; i < length; i++ ) {
					  text += possible.charAt(Math.floor(Math.random() * possible.length));
				  }
				  return text;
				}				
			},
			{
				fieldLabel: _('Активность'),
				name: 'active',
				xtype: 'checkboxfield'
			},
			{
				xtype: 'combobox',
				fieldLabel: _('Тип купона'),
				name: 'mode',
				displayField: 'name',
				valueField: 'id',
				allowBlank: false,
				forceSelection: true,
				editable: false,
				queryMode: 'local',
				store: 'saleCouponModeStore'
			},			
			{
				xtype: 'combobox',
				fieldLabel: _('Скидка'),
				name: 'discount_id',
				displayField: 'name',
				valueField: 'id',
				allowBlank: false,
				editable: false,
				forceSelection: true,
				store: 'saleDiscountStore'
			},
			{
				fieldLabel: _('Комментарий'),
				name: 'describ',
				xtype: 'textarea'
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

		this.title = this.record?Config.Lang.edit+'"'+this.record.get('name')+'"':_('Новый купон'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.Coupon');
		this.callParent();
		this.getComponent('form').getForm().loadRecord( this.record );
	}	
	  
});