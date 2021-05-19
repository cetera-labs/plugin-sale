document.addEventListener('DOMContentLoaded', function () {

    var Ext = Ext || null;
    if (!Ext) return;

    Ext.onReady(function(){
        
        panel = Ext.getCmp('admin-toolbar');
        if (!panel) return;

        panel.insert(panel.items.length-4, {
            xtype: 'buttongroup',
            title: _('Интернет-магазин'),
            items: [  
                {
                    xtype: 'button',
                    scale: 'medium',
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
                },
                {
                    xtype: 'button',
                    scale: 'medium',
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
                }            
            ]
        });

    });

});