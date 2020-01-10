Ext.define('Plugin.sale.model.Payment', {
	
    extend: 'Ext.data.Model',
	requires: 'Plugin.sale.model.Delivery',
	
    fields: [
        {name:'id', type: 'int'},
        {name:'name', type: 'string'}, 
        {name:'active', type: 'boolean'},	
		{name:'note', type: 'string'},		
		{name:'picture', type: 'string'},	
		{name:'tag', type: 'int', defaultValue: 100},
		{name:'delivery_methods', type: 'string'},
		{name:'person_types', type: 'string'},
		{name:'gateway', type: 'string'},
		{name:'gateway_params'}	
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/plugins/sale/data_payment.php',
            update  : '/plugins/sale/data_payment.php?action=update',
            create  : '/plugins/sale/data_payment.php?action=create',
            destroy : '/plugins/sale/data_payment.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }
    }	
}); 