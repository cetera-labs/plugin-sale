Ext.define('Plugin.sale.Discounts', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.Discount',
	alias: 'widget.sale.discounts',
	
	title: _('Скидки'),
	
    columns: [
        {text: "ID",       width: 50, dataIndex: 'id'},
		{text: _('Акт.'),     width: 60, dataIndex: 'active', renderer: function (value) { if (value) return _('Да'); else return _('Нет'); }},
		{text: _('Величина'),    width: 100, dataIndex: 'value_text'},		
        {text: _('Название'), flex: 1, dataIndex: 'name'},
		{text: _('Приоритет'),    width: 60, dataIndex: 'priority'}		
    ],
	
	editWindowClass: 'Plugin.sale.DiscountEdit',

	store: 'saleDiscountStore'	
		  
});