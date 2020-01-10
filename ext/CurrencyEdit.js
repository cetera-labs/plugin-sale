Ext.define('Plugin.sale.CurrencyEdit', {
    extend:'Ext.Window',

    modal: true,
    autoShow: true,
    width: 500,
    minWidth: 400,	
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
				fieldLabel: _('Валюта'),
				name: 'code',
				maxLength: 3,
				regex: /^[A-Z][A-Z][A-Z]$/i,
				allowBlank: false
			},		
			{
				fieldLabel: _('Название'),
				name: 'name',
				allowBlank: false
			},
			{
				fieldLabel: _('Базовая'),
				name: 'prime',
				xtype: 'checkboxfield'
			},		
			{
				xtype: 'numberfield',
				fieldLabel: _('Сортировка'),
				allowDecimals: false,
				step: 10,
				name: 'sort',
				allowBlank: false
			},
			{
				xtype: 'numberfield',
				fieldLabel: _('Номинал'),
				allowDecimals: false,
				step: 10,
				value: 1,
				name: 'rate_cnt',
				allowBlank: false
			},
			{
				xtype: 'numberfield',
				fieldLabel: _('Курс'),
				allowDecimals: true,
				step: 10,
				value: '1.0',
				name: 'rate',
				allowBlank: false
			},
			{
				fieldLabel: _('Шаблон'),
				name: 'template',
				value: '#',
				allowBlank: false
			}
		],
		
		buttons: [
			{
				text    : _('OK'),
				handler : function() {
					var f = this.up('form').getForm();
					if (!f.isValid()) return;
					f.updateRecord();
					if (!f.getRecord().getId()) this.up('window').fireEvent('recordcreated', f.getRecord());
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

		this.title = this.record?_('Редактировать')+' "'+this.record.get('name')+'"':_('Новая валюта'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.Currency');
		this.callParent();
		this.getComponent('form').getForm().loadRecord( this.record );
	}
	  
});