Ext.define('Plugin.sale.OrderPropsEdit', {
    extend:'Ext.Window',
	requires: ['Plugin.sale.model.PersonType','Plugin.sale.model.OrderPropsGroup'],

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
				fieldLabel: 'Alias',
				name: 'alias',
				allowBlank: false
			},			
			{
				fieldLabel: _('Активность'),
				name: 'active',
				xtype: 'checkboxfield'
			},	
			{
				fieldLabel: _('Обязательное'),
				name: 'required',
				xtype: 'checkboxfield'
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
				},
				listeners: {
					change: {
						fn: function(elm, value) {
							var gp = elm.up('form').getComponent('group');
							
							gp.getStore().filters.getAt(0).person_type_id = value;
							gp.getStore().filter();
							if ( gp.getStore().find('id', gp.getValue())<0 )
							{
								if (gp.getStore().getAt(0))
									gp.setValue( gp.getStore().getAt(0).getId() );
									else gp.setValue('');
							}														
						}
					}
				}				
			},	
			{
				itemId: 'group',
				fieldLabel: _('Группа'),
				name: 'group_id',
				xtype: 'combobox',
				displayField: 'name',
				valueField: 'id',
				editable: false,				
				store: {
					autoLoad: true,
					model: 'Plugin.sale.model.OrderPropsGroup',
					filters: [{
						filterFn: function(item) {
							return (item.get('person_type_id') == this.person_type_id)?true:false;
						},
						person_type_id: 0
					}],					
				}
			},	
			{
				fieldLabel: _('Тип'),
				name: 'type',
				xtype: 'combobox',
				displayField: 'name',
				valueField: 'id',
				editable: false,				
				store: {
					fields: ['id', 'name'],
					data: [
						{id: 'TEXT', name:_("Текстовое")},
						{id: 'CHECKBOX', name:_("Checkbox")},
						{id: 'CITY', name:_("Город")}
					],
					proxy: {
						type: 'memory'
					}	
				}
			},
			{
				fieldLabel: _('Значение по умолчанию'),
				name: 'default_value'
			},	
			{
				boxLabel: _('использовать как e-mail'),
				name: 'is_email',
				xtype: 'checkboxfield'
			},	
			{
				boxLabel: _('использовать как телефон'),
				name: 'is_phone',
				xtype: 'checkboxfield'
			},
			{
				boxLabel: 'использовать как имя пользователя',
				name: 'is_login',
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
				xtype: 'textarea',
				fieldLabel: _('Описание'),
				name: 'note'
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

		this.title = this.record?_('Редактировать')+' "'+this.record.get('name')+'"':_('Новое свойство'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.OrderProps');
		this.callParent();
		this.getComponent('form').getForm().loadRecord( this.record );
	}
	  
});