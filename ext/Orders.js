Ext.define('Plugin.sale.Orders', {

    alias : 'widget.sale_orders',

    extend:'Ext.grid.Panel',
	requires: 'Plugin.sale.model.Order',
	
    columns: [
        {text: "ID",         width: 50, dataIndex: 'id'},
        {text: _("Дата"),       width: 120, dataIndex: 'date'},		
        {
			text: _("Статус заказа"), width: 150, dataIndex: 'status_text',
			getSortParam: function(){ return 'status'; }
		},
		{
			text: _("Статус оплаты"), width: 150, dataIndex: 'paid_text',
			getSortParam: function(){ return 'paid'; }
		},
		{
			text: _("Сумма"),  width: 100, dataIndex: 'total', sortable: false
		},		
        {
			text: _("Покупатель"), 
			flex: 3, 
			dataIndex: 'buyer', 
		}
    ],
	
    border: false,
        
	_plugins: [{
            ptype: 'rowexpander',
            rowBodyTpl : new Ext.XTemplate(
				'<div style="padding: 10px"><table cellpadding="3" class="stats">',
				'<tr>',
					'<td class="total">'+_('Товар')+'</td><td class="total">'+_('Цена')+'</td><td class="total">'+_('Кол-во')+'</td><td class="total">'+_('Стоимость')+'</td>',
				'</tr>',				
				'<tpl for="products">',  
					'<tr>',
						'<td>',
							'{name}',
							'<tpl for="options">', 
								'<br>{name}: {value}',
							'</tpl>',
						'</td><td>{price}</td><td>{quantity}</td><td>{sum}</td>',
					'</tr>',
				'</tpl>',
				'<tr>',
				'<td colspan="3" class="total">'+_('Всего')+':</td><td class="total">{products_cost}</td>',
				'</tr>',				
				'</table></div>')
    }],

    plugins: {
        rowexpander: true
    },
    itemConfig: {
        body: {
            tpl: new Ext.XTemplate(
				'<div style="padding: 10px"><table cellpadding="3" class="stats">',
				'<tr>',
					'<td class="total">'+_('Товар')+'</td><td class="total">'+_('Цена')+'</td><td class="total">'+_('Кол-во')+'</td><td class="total">'+_('Стоимость')+'</td>',
				'</tr>',				
				'<tpl for="products">',  
					'<tr>',
						'<td>',
							'{name}',
							'<tpl for="options">', 
								'<br>{name}: {value}',
							'</tpl>',
						'</td><td>{price}</td><td>{quantity}</td><td>{sum}</td>',
					'</tr>',
				'</tpl>',
				'<tr>',
				'<td colspan="3" class="total">'+_('Всего')+':</td><td class="total">{products_cost}</td>',
				'</tr>',				
				'</table></div>')
        }
    },    

    initComponent: function(){
        
        this.store = Ext.create('Ext.data.Store', {
            model: 'Plugin.sale.model.Order',
			pageSize: Cetera.defaultPageSize,
			sorters: [{property: "date", direction: "DESC"}],
			remoteSort: true,
        });	

        this.editAction = Ext.create('Ext.Action', {
            iconCls: 'icon-edit', 
            text: _('Подробно'),
            disabled: true,
            scope: this,
            handler: function(widget, event)
            {
                var rec = this.getSelectionModel().getSelection()[0];
                if (rec) this.edit( rec );
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
                if (rec)  Ext.MessageBox.confirm(_('Удалить заказ'), _('Вы уверены?'), function(btn) {
					if (btn == 'yes') rec.destroy();
				}, this);    

            }
        });		
		
        Ext.apply(this, {
			
            dockedItems: [
				{
					xtype: 'toolbar',
					items: [
						{
							tooltip: Config.Lang.reload,
							iconCls: 'icon-reload',
							handler: function(btn) { btn.up('grid').getStore().load(); }
						},
						this.editAction,
						this.deleteAction
					]
				},{
					xtype: 'pagingtoolbar',
					store: this.store,   // same store GridPanel is using
					dock: 'bottom',
					displayInfo: true,
					items: [
						_('Поиск') + ': ', 
						Ext.create('Cetera.field.Search', {
							itemId: 'search',
							store: this.store,
							paramName: 'query',
							width:200
						}),
						_('Дата заказа') + ': ', 
						{
							xtype: 'datefield',
							format: 'Y-m-d',
							width: 100,
							listeners: {
								change: function(el, value) {
									if (!el.validate()) return;
									var store = el.up('grid').getStore();
									store.proxy.extraParams = store.proxy.extraParams || {};
									store.proxy.extraParams['date_from'] = el.getRawValue();
									store.load();
								}
							}
						},
						' - ',
						{
							xtype: 'datefield',
							format: 'Y-m-d',
							width: 100,
							listeners: {
								change: function(el, value) {
									if (!el.validate()) return;
									var store = el.up('grid').getStore();
									store.proxy.extraParams = store.proxy.extraParams || {};
									store.proxy.extraParams['date_to'] = el.getRawValue();
									store.load();
								}
							}							
						},
						_('Статус заказа') + ': ', 
						{
							xtype: 'combobox',
							store: {
								autoDestroy: true,
								autoLoad: true,
								fields: ['id', 'name'],
								proxy: {
									type: 'ajax',
									url: '/plugins/sale/data_orders.php?action=get_status_list',
									reader: {
										type: 'json',
										root: 'rows'
									}
								}                   
							},
							valueField:'id',
							displayField:'name',
							listeners: {
								change: function(el, value) {
									if (!el.validate()) return;
									var store = el.up('grid').getStore();
									store.proxy.extraParams = store.proxy.extraParams || {};
									store.proxy.extraParams['status'] = el.getValue();
									store.load();
								}
							}							
						}						
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
                this.editAction,
				this.deleteAction
            ]
        });

        this.getSelectionModel().on({
            selectionchange: function(sm, selections) {
                if (selections.length) {
                    this.editAction.enable();  
					this.deleteAction.enable();  					
                } else {
                    this.editAction.disable();
					this.deleteAction.disable();  					
                }
            },
            scope: this
        });
		
			  
        this.store.load();
        
        this.callParent(arguments);

    },

    edit: function( record ) {
    
        var window = Ext.create('Plugin.sale.OrderEdit',{
            record: record,
			store: this.store
        });
    
    },
	
	setStore: function(store) {
		this.reconfigure(store);
		
		var bbars = this.getDockedItems('toolbar[dock="bottom"]');		
		if (bbars.length) {	
			bbars[0].bindStore(this.store);
			bbars[0].getComponent('search').store = this.store;
		}		
	}
		  
});