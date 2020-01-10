Ext.define('Plugin.sale.FilterEdit', {
    extend:'Ext.Window',
	requires: 'Cetera.field.Folder',

    modal: true,
    autoShow: true,
    width: 700,
    height: 200,
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
				itemId: 'field_id',
				xtype: 'combobox',
				fieldLabel: _('Поле'),
				name: 'field_id',
				displayField: 'name',
				valueField: 'id',
				allowBlank: false,
				forceSelection: true,
				queryMode: 'local',
				store: 'saleFilterFieldsStore',
				listeners: {
					change: {
						fn: function(elm, value) {
							var rec = elm.getStore().getById(value);
							var ft = elm.up('form').getComponent('filter_type');
							
							ft.getStore().filters.getAt(0).field_type = rec.get('type');
							ft.getStore().filter();
							
							if ( ft.getStore().find('id', ft.getValue())<0 )
							{
								if (ft.getStore().getAt(0))
									ft.setValue( ft.getStore().getAt(0).getId() );
							}							
						}
					}
				}					
			},	
			{
				itemId: 'filter_type',
				xtype: 'combobox',
				fieldLabel: _('Вид фильтра'),
				name: 'filter_type',
				displayField: 'name',
				valueField: 'id',
				allowBlank: false,
				forceSelection: true,
				editable: false,
				queryMode: 'local',
				store: 'saleFilterTypesStore'
			},				
			{
				itemId: 'catalog_id',
				xtype: 'folderfield',
				fieldLabel: _('Показывать только в разделе'),
				name: 'catalog_id'
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
				xtype: 'hiddenfield',
				name: 'field_name',
				itemId: 'field_name'
			},
			{
				xtype: 'hiddenfield',
				name: 'filter_type_name',
				itemId: 'filter_type_name'
			},
			{
				xtype: 'hiddenfield',
				name: 'catalog_name',
				itemId: 'catalog_name'
			}
		],
		
		buttons: [
			{
				text    : _('OK'),
				handler : function() {
					var f = this.up('form');
					if (!f.getForm().isValid()) return;
										
					f.getComponent('field_name').setValue( f.getComponent('field_id').getDisplayValue() );
					f.getComponent('filter_type_name').setValue( f.getComponent('filter_type').getDisplayValue() );
					f.getComponent('catalog_name').setValue( f.getComponent('catalog_id').getDisplayValue() );
					
					f.getForm().updateRecord();
					
					if (!f.getForm().getRecord().getId()) this.up('window').fireEvent('recordcreated', f.getForm().getRecord());
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

		this.title = this.record?_('Редактировать поле'):_('Добавить поле'); 
		if (!this.record) this.record = Ext.create('Plugin.sale.model.Filter');
		this.callParent();	
		
		this.getComponent('form').getForm().loadRecord( this.record );
		
	}
	  
});