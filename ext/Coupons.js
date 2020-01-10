Ext.define('Plugin.sale.Coupons', {

    extend:'Cetera.grid.Abstract',
	requires: 'Plugin.sale.model.Coupon',
	alias: 'widget.sale.coupons',
	
	title: _('Купоны'),
	
    columns: [
        {text: "ID",       width: 50, dataIndex: 'id'},
		{text: _('Акт.'),  width: 60, dataIndex: 'active', renderer: function (value) { if (value) return _('Да'); else return _('Нет'); }},
		{text: _('Код'),   width: 150, dataIndex: 'code'},			
		{text: _('Тип'),   width: 150, dataIndex: 'mode_text'},
		{text: _('Скидка'),width: 200, dataIndex: 'discount_name'},			
		{text: _('Комментарий'),flex:  1, dataIndex: 'describ'},			
    ],
	
	editWindowClass: 'Plugin.sale.CouponEdit',

	store: 'saleCouponStore'	
		  
});

Ext.create('Ext.data.Store', {
    storeId: 'saleCouponModeStore',
	fields: ['name'],
	proxy: {
		 type: 'ajax',
		 url: '/plugins/sale/data_coupon.php?modes',
		 reader: {
			 type: 'json',
			 root: 'rows'
		 }
	},
	autoLoad: true	
});