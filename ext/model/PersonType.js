Ext.define('Plugin.sale.model.PersonType', {
	
    extend: 'Ext.data.Model',
	
    fields: [
        {name:'id', type: 'int'},
        {name:'name', type: 'string'}, 
		{name:'active', type: 'boolean'},
		{name:'sort', type: 'int', defaultValue: 100}	
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/cms/plugins/sale/data_person_type.php',
            update  : '/cms/plugins/sale/data_person_type.php?action=update',
            create  : '/cms/plugins/sale/data_person_type.php?action=create',
            destroy : '/cms/plugins/sale/data_person_type.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows',
            rootProperty: 'rows'
        }
    }	
}); 