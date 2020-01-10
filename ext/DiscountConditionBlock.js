Ext.define('Plugin.sale.DiscountConditionBlock', {

    extend:'Cetera.field.Panel',
	alias: 'widget.sale.discountcondition',
	
    onResize : function(w, h){
        this.callParent(arguments);
    },	
	
	getValue: function() {
		var v = this.panel.getValues();	
		
		var values = {
			logic: v.logic,
			logic2: v.logic2,
			conditions: []
		};

		this.conditions.items.each(function(item) {
			Ext.Array.push( values.conditions, item.getValues() );
		}, this);
		
		return Ext.JSON.encode(values);
	},
	
	setValue: function(value) {
		if (!value) return;
		var values = Ext.JSON.decode(value);
		this.panel.getForm().setValues(values);
		Ext.Array.each(values.conditions, function(val) {
			var c = Ext.create('Plugin.sale.DiscountConditionContainer');
			c.getForm().setValues(val);
			this.conditions.add(c);
		}, this);
	},
	
	getPanel : function() {
		
		this.conditions = Ext.create('Ext.Panel',{
			layout: 'anchor',
			//overflowY: 'auto',
			autoScroll: true,
			flex: 1,
			
			defaults: {
				anchor: '100%'
			}
		});
		
		this.btnAdd = Ext.create('Ext.Button',{
			text: _('Добавить условие'),
			scope: this,
			handler: function() {						
				this.conditions.add(Ext.create('Plugin.sale.DiscountConditionContainer'));
			}			
		});
		
        return new Ext.form.Panel({
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			border: false,
			bodyStyle : 'background: none',
			items: [
				{
					xtype: 'fieldcontainer',
					hideEmptyLabel: true,
					layout: 'hbox',
					defaults: {
						hideEmptyLabel: true,
						xtype: 'combobox',
					},								
					items: [
						{
							flex: 1,
							name: 'logic',
							editable: false,
							store: [
								['and',_('все условия')],
								['or',_('любое из условий')]
							],	
							value: 'and'
						},
						{
							xtype: 'splitter'
						},
						{
							flex: 1,
							name: 'logic2',
							editable: false,
							store: [
								[0, _('выполнено(ы)')],
								[1, _('не выполнено(ы)')]
							],	
							value: 0
						},
						{
							xtype: 'splitter'
						},						
						this.btnAdd
					]
				},
				{
					xtype: 'splitter'
				},				
				this.conditions
			]
        }); 		
		
	}
			  
});