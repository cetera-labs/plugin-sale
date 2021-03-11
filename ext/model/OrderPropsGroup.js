Ext.define('Plugin.sale.model.OrderPropsGroup', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
        {name:'name', type: 'string'}, 
		{name:'sort', type: 'int', defaultValue: 100},
		{name:'person_type_id', type: 'int', defaultValue: 1},
		{name:'person_type', type: 'string', persist: false}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/cms/plugins/sale/data_order_props_groups.php',
            update  : '/cms/plugins/sale/data_order_props_groups.php?action=update',
            create  : '/cms/plugins/sale/data_order_props_groups.php?action=create',
            destroy : '/cms/plugins/sale/data_order_props_groups.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows',
            rootProperty: 'rows'
        }
    }	
}); 