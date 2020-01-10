Ext.define('Plugin.sale.AbstractGrid', {

    extend:'Ext.grid.Panel',
		
	initComponent: function() {
		
        this.editAction = Ext.create('Ext.Action', {
            iconCls: 'icon-edit', 
            text: 'Изменить',
            disabled: true,
            scope: this,
            handler: function(widget, event)
            {
                var rec = this.getSelectionModel().getSelection()[0];
                if (rec) this.edit( rec );
            }
        });		
		
        this.addAction = Ext.create('Ext.Action', {
            iconCls: 'icon-new', 
            text: 'Добавить',
            scope: this,
            handler: function(widget, event)
            {
				this.edit();
            }
        });	

		this.deleteAction = Ext.create('Ext.Action', {
            iconCls: 'icon-delete', 
            text: 'Удалить',
            disabled: true,
            scope: this,
            handler: function(widget, event)
            {
                Ext.MessageBox.confirm(Config.Lang.delete, Config.Lang.r_u_sure, function(btn) {
                    if (btn == 'yes') {
                        var rec = this.getSelectionModel().getSelection()[0];
                        if (rec) this.getStore().remove(rec);   
                    }
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
						this.addAction,
						this.editAction,
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
                this.addAction,
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
        
        this.callParent(arguments);

    },
	
    edit: function( record ) {
    
        var window = Ext.create(this.editWindowClass, {
			record: record,
            listeners: {
                scope: this,
                recordcreated: function(r) {
                    this.getStore().add(r);
                }
            }			
        });
    
    }	
		  
});