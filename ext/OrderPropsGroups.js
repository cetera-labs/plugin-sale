Ext.define('Plugin.sale.OrderPropsGroups', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.OrderPropsGroup',
	alias: 'widget.sale.order.props.groups',
	
	editWindowClass: 'Plugin.sale.OrderPropsGroupEdit',
	
    columns: [
		{text: "ID", width: 50, dataIndex: 'id'},
		{text: _("Сорт."), width: 60, dataIndex: 'sort'},
		{text: _("Тип плательщика"),  flex: 1, dataIndex: 'person_type'},
        {text: _("Название"),  flex: 2, dataIndex: 'name'}
    ],
	
	sortableColumns: false,
	
	store: {
		model: 'Plugin.sale.model.OrderPropsGroup',
		sorters: [{property: "sort", direction: "ASC"}],
		remoteSort: false,
		autoLoad: true,
		autoSync: true			
	}
		  
});