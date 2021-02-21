Ext.define('Plugin.sale.OrderRefund', {
    
    extend:'Ext.Window',
    
    modal: true,
    autoShow: true,
    width: '60%',
    height: '60%',
    minWidth: 400,
    minHeight: 300,

    layout: {
        type: 'vbox',
        padding: 10,
        align : 'stretch',
        pack  : 'start'
    },  
    
    initComponent: function(){

		this.title = _('Возврат средств. Заказ №')+this.record.getId() + ' '+ _('от') +' ' + this.record.get('date'); 
		
        this.checkAll = Ext.widget({
            xtype: 'checkboxfield',
            boxLabel  : _('вернуть в полном объёме'),
        });
        
        this.checkAll.on('change', function(elm, newValue){
            this.products.setDisabled(newValue);
        }, this);        
        
        this.products = Ext.create('Ext.grid.Panel', {
            title: _('Товары'),
            flex: 1,
            columns: [
                { 
                    xtype : 'checkcolumn', 
                    text : '', 
                    width: 50,
                    dataIndex : 'checked' 
                },            
                {
                    text: _('Товар'), 
                    flex: 1, 
                    dataIndex: 'name', 
                },
                {
                    text: _('Цена'), 
                    width: 100,
                    dataIndex: 'price'
                },
                {
                    text: _('Кол-во'), 
                    width: 75,
                    dataIndex: 'quantity', 
                    editor: {
                        xtype: 'numberfield',
                        allowBlank: false,
                        allowDecimals: false,
                        minValue: 1
                    }
                },
                {
                    text: _('Стоимость'), 
                    width: 100, 
                    renderer: function (value,meta,record) {
                        return record.get('price') * record.get('quantity');
                    }
                }	
            ],
            store: {
                fields: [
                   {name: 'checked', type: 'boolean'},
                   {name: 'name', persist: false},
                   {name: 'price', type: 'float'},
                   {name: 'quantity', type: 'integer'},
                ],
                data: this.record.getData().products,
            },            
            plugins: [
                {ptype: 'cellediting', clicksToEdit: 1}
            ],          
        });
        
		Ext.apply(this, {
			items: [
                this.checkAll,
                this.products,
            ],
            buttons: [
                {
                    text    : _('OK'),
                    handler : function() {
                        
                        var w = this.up('window');
                        var products = [];
                        
                        w.products.getStore().each( function(rec){
                            if (rec.get('checked')) {
                                products.push(rec.getData());
                            }
                        }, this );
                        
                        if (!products.length) {
                            return;
                        }                        
                        
                        Ext.MessageBox.confirm(_('Вернуть средства'), _('Вы уверены'), function(btn) {
                            if (btn == 'yes') {
                                
                                w.setLoading(true);
                                Ext.Ajax.request({
                                    url: '/plugins/sale/data_payment.php?action=refund',
                                    method: 'POST',
                                    scope : this,
                                    jsonData: {
                                        order_id: w.record.getId(),
                                        full: w.checkAll.getValue(),
                                        products: products
                                    },
                                    success: function(response){
                                        w.destroy();
                                    },
                                    callback: function() {
                                        w.setLoading(false);
                                    }
                                });                            
                            }
                        }, this);                        

                    }
                },{
                    text    : _('Отмена'),
                    handler : function() {
                        this.up('window').destroy();
                    }
                }            
            ]
		});
		
		this.callParent();		
		
    },    
    
});