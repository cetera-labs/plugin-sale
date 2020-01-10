Ext.define('Plugin.sale.Currency', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.Currency',
	alias: 'widget.sale.currency',

	title: _('Валюты'),
	
	editWindowClass: 'Plugin.sale.CurrencyEdit',
	
    columns: [
		{text: "ID", width: 50, dataIndex: 'id'},
		{text: _("Валюта"),   width: 60, dataIndex: 'code'},
		{text: _("Сорт."), width: 100, dataIndex: 'sort'},
		{text: _("Базовая"),  width: 60, dataIndex: 'prime', renderer: function (value) { if (value) return _('Да'); else return _('Нет'); }},
		{text: _("Название"),  flex: 1, dataIndex: 'name'},
		{text: _("Номинал"),   width: 60, dataIndex: 'rate_cnt'},
		{text: _("Курс"),   width: 60, dataIndex: 'rate'}
    ],
	
	store: {
		model: 'Plugin.sale.model.Currency',
		sorters: [{property: "sort", direction: "ASC"}],
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
	}	
		  
});