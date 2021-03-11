<?php
function editor_text_currency_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Ext.form.ComboBox',{
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        <? if ($field_def['type'] == FIELD_TEXT) : ?>
                        <? endif; ?>
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>',
						editable: false,
                        valueField: 'code',
                        displayField: 'name',
                        store: Ext.create('Ext.data.JsonStore',{
                            autoDestroy: true,
                            autoLoad: true,
                            fields: [
                                {name:'id', type: 'int'},
                                {name:'code', type: 'string'},
                                {name:'name', type: 'string'}, 
                            ],
                            
                            proxy: {
                                type: 'ajax',
                                simpleSortMode: true,
                                api: {
                                    read    : '/cms/plugins/sale/data_currency.php',			
                                },		
                                reader: {
                                    type: 'json',
                                    root: 'rows',
                                    rootProperty: 'rows'
                                }
                            }                  
                        }),						
                    })
<?
    return 25;
}
?>