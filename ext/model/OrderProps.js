Ext.define('Plugin.sale.model.OrderProps', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
		{name:'active', type: 'boolean'},	
		{name:'required', type: 'boolean'},
        {name:'name', type: 'string'}, 
		{name:'sort', type: 'int', defaultValue: 100},
		{name:'person_type_id', type: 'int', defaultValue: 1},
		{name:'person_type', type: 'string', persist: false},
		{name:'group_id', type: 'int'},
		{name:'group_name', type: 'string', persist: false},
		{name:'type', type: 'string', defaultValue: 'TEXT'},
		{name:'alias', type: 'string'},
		{name:'note', type: 'string'},
		{name:'default_value', type: 'string'},
		{name:'is_email', type: 'boolean'},
		{name:'is_phone', type: 'boolean'},
		{name:'is_login', type: 'boolean'}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/cms/plugins/sale/data_order_props.php',
            update  : '/cms/plugins/sale/data_order_props.php?action=update',
            create  : '/cms/plugins/sale/data_order_props.php?action=create',
            destroy : '/cms/plugins/sale/data_order_props.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows',
            rootProperty: 'rows'
        }
    }	
}); 