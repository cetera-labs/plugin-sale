Ext.define('Plugin.sale.OrdereditGoods', {

    extend:'Ext.grid.Panel',
	alias : 'widget.order.goods',
	
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
		},		
        {
			text: _('Возвращено'), 
			width: 100, 
			dataIndex: 'sum_refund', 
		}	

    ],
	
    border: true,
	
	plugins: [
		{ptype: 'cellediting', clicksToEdit: 2}
	],
	
    initComponent: function(){
        	
		this.store = Ext.create('Ext.data.Store', {
			fields: [
			   {name: 'name', persist: false},
			   {name: 'price', type: 'float'},
			   {name: 'quantity', type: 'integer'},
			   {name: 'sum', type: 'float', persist: false},
               {name: 'sum_refund', type: 'float', persist: false},
			   'add_product'
			],
			data: this.record.getData().products,
			autoSync: true,
			proxy: {
				type: 'ajax',
				api: {
					create:  '/plugins/sale/data_order_products.php?action=create&order=' + this.record.getId(),
					update:  '/plugins/sale/data_order_products.php?action=update&order=' + this.record.getId(),
					destroy: '/plugins/sale/data_order_products.php?action=destroy&order=' + this.record.getId()
				},
				reader: {
					type: 'json',
					root: 'rows'
				}
			},
			listeners: {
				write: {
					fn: function() {
						this.fireEvent('order_updated');
					},
					scope: this
				}				
			}		
		});		
		
		this.addAction = Ext.create('Ext.Action', {
            iconCls: 'icon-plus', 
            text: _('Добавить товар'),
            scope: this,
            handler: function(widget, event) {

				if (!this.siteTree) {
					this.siteTree = Ext.create('Cetera.window.SiteTree', {
						materials: 1,
						url: '/plugins/sale/data_tree.php?1=1'
					});
					this.siteTree.on('select', function(res) {
						this.store.add({
							'add_product': res.id
						});
					},this);
				}
				this.siteTree.show(); 			
			
            }
        });			
		
		this.deleteAction = Ext.create('Ext.Action', {
            iconCls: 'icon-delete', 
            text: _('Удалить'),
            disabled: true,
            scope: this,
            handler: function(widget, event)
            {
                var rec = this.getSelectionModel().getSelection()[0];
                if (rec)  Ext.MessageBox.confirm(_('Удалить товар из заказа'), _('Вы уверены?'), function(btn) {
					if (btn == 'yes') rec.destroy();
				}, this);    

            }
        });		
		
        Ext.apply(this, {
			
            dockedItems: [
				{
					xtype: 'toolbar',
					items: [
						this.addAction,
						this.deleteAction
					]
				}
			],
			
            viewConfig: {
                stripeRows: true,
                listeners: {
                    itemcontextmenu: {
                        fn: function(view, rec, node, index, e) {
                            e.stopEvent();
                            this.contextMenu.showAt(e.getXY());
                            return false;
                        },
                        scope: this
                    }
                }
            }			
                        
        });
              
        this.contextMenu = Ext.create('Ext.menu.Menu', {
            items: [
				this.deleteAction
            ]
        });

        this.getSelectionModel().on({
            selectionchange: function(sm, selections) {
                if (selections.length) {
					this.deleteAction.enable();  					
                } else {
					this.deleteAction.disable();  					
                }
            },
            scope: this
        });
        
        this.callParent(arguments);

    }	
});