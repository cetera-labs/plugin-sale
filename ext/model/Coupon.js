Ext.define('Plugin.sale.model.Coupon', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
        {name:'code', type: 'string'}, 
        {name:'active', type: 'boolean'},		
		{name:'mode', type: 'int', defaultValue: 1},
		{name:'discount_id', type: 'int'},
		{name:'describ', type: 'string'},
		{name:'mode_text', type: 'string', persist: false},
		{name:'discount_name', type: 'string', persist: false},
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/plugins/sale/data_coupon.php',
            update  : '/plugins/sale/data_coupon.php?action=update',
            create  : '/plugins/sale/data_coupon.php?action=create',
            destroy : '/plugins/sale/data_coupon.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }		
    }	
}); 

var cs = Ext.create('Ext.data.Store',{
	storeId: 'saleCouponStore',
	model: 'Plugin.sale.model.Coupon',
	sorters: [{property: "priority", direction: "ASC"}],
	remoteSort: false,
	autoLoad: true,
	autoSync: true,
	listeners: {
		write: {
			fn: function(store) {
				store.reload();
			}
		},
		remove: {
			fn: function(store) {
				store.reload();
			}
		}		
	}		
});

cs.getProxy().on({
	exception: function(proxy, response, operation) {
		cs.rejectChanges();
	}
});
