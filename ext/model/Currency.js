Ext.define('Plugin.sale.model.Currency', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
		{name:'code', type: 'string'},
		{name:'name', type: 'string'}, 
		{name:'prime', type: 'boolean'},
		{name:'rate', type: 'float', defaultValue: 1},
		{name:'rate_cnt', type: 'int', defaultValue: 1},
		{name:'sort', type: 'int', defaultValue: 100},
		{name:'template', type: 'string', defaultValue: '#'}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/plugins/sale/data_currency.php',
            update  : '/plugins/sale/data_currency.php?action=update',
            create  : '/plugins/sale/data_currency.php?action=create',
            destroy : '/plugins/sale/data_currency.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }
    }	
}); 