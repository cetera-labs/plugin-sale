Ext.define('Plugin.sale.PersonType', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.PersonType',
	alias: 'widget.sale.person_type',

	title: _('Типы плательщиков'),
	
	editWindowClass: 'Plugin.sale.PersonTypeEdit',
	
    columns: [
		{text: "ID", width: 50, dataIndex: 'id'},
		{text: _("Акт."),     width: 60, dataIndex: 'active', renderer: function (value) { if (value) return _('Да'); else return _('Нет'); }},
		{text: _("Сорт."), width: 100, dataIndex: 'sort'},
		{text: _("Название"),  flex: 1, dataIndex: 'name'}
    ],
	
	store: {
		model: 'Plugin.sale.model.PersonType',
		sorters: [{property: "sort", direction: "ASC"}],
		remoteSort: false,
		autoLoad: true,
		autoSync: true			
	}	
		  
});