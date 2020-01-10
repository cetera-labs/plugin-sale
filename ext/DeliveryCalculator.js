Ext.define('Plugin.sale.DeliveryCalculator', {
    extend:'Ext.Window',

	requires: [
		'Plugin.sale.model.Currency',
		'Plugin.sale.model.PersonType'
	],	
	
    modal: true,
    autoShow: true,
    width: 600,
    minWidth: 400,
    minHeight: 100,
	layout: 'fit',
			
    initComponent: function(){
				
		Ext.apply(this, {
			title: this.data.name,
			items: {
				xtype: 'form',		
				itemId: 'form',
				layout: 'anchor',
				defaults: {
					anchor: '100%',
					labelWidth: 200,
					hideEmptyLabel: false
				},
				border: false,
				defaultType: 'textfield',
				bodyPadding: 10,		
				bodyCls: 'x-window-body-default',
				
				items: this.data.params,
			
				buttons: [
					{
						text    : Config.Lang.ok,
						handler : function() {
							this.up('window').fireEvent('dataReady', this.up('form').getForm().getValues() );
							this.up('window').destroy();
						}
					},{
						text    : Config.Lang.cancel,
						handler : function() {
							this.up('window').destroy();
						}
					}
				]
			}
		});		
		
		this.callParent();
		
		this.getComponent('form').getForm().setValues( this.values );
		
	}
	  
});