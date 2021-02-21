Ext.define('Plugin.sale.OrderEdit', {
    
    extend:'Ext.Window',
    
    modal: true,
    autoShow: true,
    width: '60%',
    height: '60%',
    minWidth: 400,
    minHeight: 300,

    layout: {
        type: 'vbox',
        padding: 2,
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
            
            columns: [
                {
                    text: _('Товар'), 
                    flex: 1, 
                    dataIndex: 'name', 
                },
                {
                    text: _('Цена'), 
                    width: 100,
                    dataIndex: 'price',
                    editor: {
                        xtype: 'numberfield',
                        allowBlank: false,
                        allowDecimals: true,
                        minValue: 0
                    }
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
                    dataIndex: 'sum', 
                }	
            ],
            store: {
                fields: [
                   {name: 'name', persist: false},
                   {name: 'price', type: 'float'},
                   {name: 'quantity', type: 'integer'},
                   {name: 'sum', type: 'float', persist: false},
                ],
                data: this.record.getData().products,
            },            
            plugins: [
                {ptype: 'cellediting', clicksToEdit: 2}
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