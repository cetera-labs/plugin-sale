Ext.create('Ext.data.Store', {
    storeId: 'saleFilterFieldsStore',
	fields: [
		{ name:'id', type: 'int'},
		'name',
		{ name:'type_id', type: 'int'},
		{ name:'type', type: 'int'}
	],
	proxy: {
		 type: 'ajax',
		 url: '/plugins/sale/data_filter_fields.php',
		 reader: {
			 type: 'json',
			 root: 'rows'
		 }
	},
	autoLoad: true	
});

Ext.create('Ext.data.Store', {
    storeId: 'saleFilterTypesStore',
	fields: [
		{ name:'id', type: 'int'}, 'name', 'fields'
	],
	proxy: {
		type: 'ajax',
		url: '/plugins/sale/data_filter_types.php',
		reader: {
			type: 'json',
			root: 'rows'
		}
	},
	filters: [{
		filterFn: function(item) {
			return (Ext.Array.indexOf( item.get('fields'), this.field_type )<0)?false:true;
		},
		field_type: 0
	}],
	autoLoad: true
});

Ext.define('Plugin.sale.Filter', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.Filter',
	alias: 'widget.sale.filter',
	
	editWindowClass: 'Plugin.sale.FilterEdit',
	
    columns: [
		{text: "ID", width: 50, dataIndex: 'field_id'},
        {text: _("Поле"),  flex: 1, dataIndex: 'field_name'},
		{text: _("Вид"),   flex: 1, dataIndex: 'filter_type_name'},
		{text: _("Только для раздела"), dataIndex: 'catalog_name', flex: 1},
		{text: _("Сортировка"), width: 150, dataIndex: 'sort'},
    ],
	
	store: {
		model: 'Plugin.sale.model.Filter',
		sorters: [{property: "sort", direction: "ASC"}],
		remoteSort: false,
		autoLoad: true,
		autoSync: true			
	}
		  
});