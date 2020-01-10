Ext.define('Plugin.sale.PersonTypeEdit', {
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
				name: 'sort',
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

		this.title = this.record?_('Редактировать')+' "'+this.record.get('name')+'"':_('Новый плательщик'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.PersonType');
		this.callParent();
		this.getComponent('form').getForm().loadRecord( this.record );
	}
	  
});