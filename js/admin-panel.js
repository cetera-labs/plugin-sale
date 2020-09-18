document.addEventListener('DOMContentLoaded', function () {

    Ext.onReady(function(){
        
        panel = Ext.getCmp('admin-toolbar');
        if (!panel) return;

        panel.insert(panel.items.length-4, '-');

        panel.insert(panel.items.length-4, {
            xtype: 'button',
            icon: '/cms/plugins/sale/images/icon_goods.png',
            text: _('Товары'),
            handler: function(btn) {
                Ext.create('Ext.window.Window', {
                    title: _('Товары'),
                    modal: true,
                    height: '90%',
                    width: '80%',
                    layout: 'fit',
                    items: Ext.create('Plugin.sale.Goods'),
                }).show();                
            }
        });

        panel.insert(panel.items.length-4, {
            xtype: 'button',
            icon: '/cms/plugins/sale/images/icon_orders.png',
            text: _('Заказы'),
            handler: function(btn) {
                Ext.create('Ext.window.Window', {
                    title: _('Заказы'),
                    modal: true,
                    height: '90%',
                    width: '80%',
                    layout: 'fit',
                    items: Ext.create('Plugin.sale.Orders'),
                }).show();                
            }            
        });

    });

});