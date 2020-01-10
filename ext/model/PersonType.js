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
            read    : '/plugins/sale/data_person_type.php',
            update  : '/plugins/sale/data_person_type.php?action=update',
            create  : '/plugins/sale/data_person_type.php?action=create',
            destroy : '/plugins/sale/data_person_type.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }
    }	
}); 