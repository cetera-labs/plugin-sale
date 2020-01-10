Ext.define('Plugin.sale.model.Filter', {
	
    extend: 'Ext.data.Model',

	
    fields: [
		{name:'id', type: 'int'},
        {name:'field_id', type: 'int'}, 
		{name:'field_type', type: 'int'},	
        {name:'filter_type', type: 'int'},	
		{name:'catalog_id', type: 'int'},		
		{name:'sort', type: 'int', defaultValue: 100},
		{name:'field_name'},
		{name:'filter_type_name'},
		{name:'catalog_name'}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/plugins/sale/data_filter.php',
            update  : '/plugins/sale/data_filter.php?action=update',
            create  : '/plugins/sale/data_filter.php?action=create',
            destroy : '/plugins/sale/data_filter.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }
    }	
}); 