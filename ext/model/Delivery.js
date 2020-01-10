Ext.define('Plugin.sale.model.Delivery', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
        {name:'name', type: 'string'}, 
        {name:'active', type: 'boolean'},		
		{name:'tag', type: 'int', defaultValue: 100},
		{name:'payment_methods', type: 'string'},	
		{name:'calculator', type: 'string'},
		{name:'calculator_params'}	
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/plugins/sale/data_delivery.php',
            update  : '/plugins/sale/data_delivery.php?action=update',
            create  : '/plugins/sale/data_delivery.php?action=create',
            destroy : '/plugins/sale/data_delivery.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }
    }	
}); 