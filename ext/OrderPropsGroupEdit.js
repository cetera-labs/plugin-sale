Ext.define('Plugin.sale.OrderPropsGroupEdit', {
    extend:'Ext.Window',
	requires: 'Plugin.sale.model.PersonType',

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
				fieldLabel: _('Тип плательщика'),
				name: 'person_type_id',
				xtype: 'combobox',
				allowBlank: false,
				displayField: 'name',
				valueField: 'id',
				editable: false,				
				store: {
					autoLoad: true,
					model: 'Plugin.sale.model.PersonType'
				}
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

		this.title = this.record?_('Редактировать')+' "'+this.record.get('name')+'"':_('Новая группа'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.OrderPropsGroup');
		this.callParent();
		this.getComponent('form').getForm().loadRecord( this.record );
	}
	  
});