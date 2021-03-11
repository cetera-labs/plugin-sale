Ext.define('Plugin.sale.model.Discount', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
        {name:'name', type: 'string'}, 
        {name:'active', type: 'boolean'},		
		{name:'priority', type: 'int', defaultValue: 1},
		{name:'value', type: 'float'},
		{name:'value_text', persist: false},
		{name:'value_type', type: 'int'},
		{name:'max_discount', type: 'float'},
		{name:'last_discount', type: 'boolean'},
		{name:'describ', type: 'string'},
		{name:'conditions', type: 'string'}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/cms/plugins/sale/data_discount.php',
            update  : '/cms/plugins/sale/data_discount.php?action=update',
            create  : '/cms/plugins/sale/data_discount.php?action=create',
            destroy : '/cms/plugins/sale/data_discount.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows',
            rootProperty: 'rows'
        }
    }	
}); 

Ext.create('Ext.data.Store',{
	storeId: 'saleDiscountStore',
	model: 'Plugin.sale.model.Discount',
	sorters: [{property: "priority", direction: "ASC"}],
	remoteSort: false,
	autoSync: true,
	autoLoad: true	
});