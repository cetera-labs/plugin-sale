Ext.define('Plugin.sale.OrderProps', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.OrderProps',
	alias: 'widget.sale.order.props',
	
	editWindowClass: 'Plugin.sale.OrderPropsEdit',
	
    columns: [
		{text: "ID", width: 50, dataIndex: 'id'},
		{text: _("Акт."),  width: 60, dataIndex: 'active', renderer: function (value) { if (value) return _('Да'); else return _('Нет'); }},
		{text: _("Обяз."),  width: 60, dataIndex: 'required', renderer: function (value) { if (value) return _('Да'); else return _('Нет'); }},
		{text: "Alias",  flex: 1, dataIndex: 'alias'},
		{text: _("Тип плательщика"),  flex: 1, dataIndex: 'person_type'},
		{text: _("Группа"),  flex: 1, dataIndex: 'group_name'},
		{text: _("Сорт."), width: 60, dataIndex: 'sort'},
        {text: _("Название"),  flex: 3, dataIndex: 'name'}
    ],
	
	sortableColumns: false,
	
	store: {
		model: 'Plugin.sale.model.OrderProps',
		autoLoad: true,
		autoSync: true			
	}
		  
});