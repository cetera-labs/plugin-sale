Ext.define('Plugin.sale.model.Order', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
        {name:'user_id', type: 'int'}, 
        {name:'status', type: 'int'},	
		{name:'paid', type: 'int'},		
		{name:'status_text', persist: false},
		{name:'paid_text', persist: false},				
        'date',
		{name:'total', persist: false},	
		{name:'products', persist: false},	
		{name:'props', persist: true},	
		{name:'buyer', persist: false},	
		{name:'payment_id', type: 'int'},
		{name:'payment_data', persist: false},	
        {name:'payment_refund_allowed', persist: false},
		{name:'delivery_id', type: 'int'},
		{name:'delivery_data', persist: false},
		'delivery_cost',
		'delivery_note',
		{name:'products_cost', persist: false},
		'refresh',
		'note'
    ],
	
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/cms/plugins/sale/data_orders.php',
            update  : '/cms/plugins/sale/data_orders.php?action=update',
            destroy : '/cms/plugins/sale/data_orders.php?action=delete'
        },		
        reader: {
			type: 'json',
            root: 'rows',
            rootProperty: 'rows'
        }
    }	
}); 