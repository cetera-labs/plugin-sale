Ext.define('Plugin.sale.Delivery', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.Delivery',
	
    columns: [
        {text: "ID",       width: 50, dataIndex: 'id'},
		{text: _('Акт.'),     width: 60, dataIndex: 'active', renderer: function (value) { if (value) return _('Да'); else return _('Нет'); }},
		{text: _('Сорт.'),    width: 60, dataIndex: 'tag'},		
        {text: _('Название'), flex: 1, dataIndex: 'name'}
    ],
	
    border: false,
	
	editWindowClass: 'Plugin.sale.DeliveryEdit',

	store: {
		model: 'Plugin.sale.model.Delivery',
		sorters: [{property: "tag", direction: "ASC"}],
		remoteSort: false,
		autoLoad: true,
		autoSync: true			
	}	
		  
});