document.addEventListener('DOMContentLoaded', function () {

	if (jQuery('.x-sale-city').length)
	{
		jQuery('.x-sale-city').autocomplete({
			minLength: 2,
			source: '/plugins/sale/ajax.php?action=city_lookup'
		});
	}

	jQuery('.x-sale-slider-range').each(function(key,value){				
				
		jQuery(value).slider({
			range: true,
			min: parseInt(jQuery(value).attr('data-start')),
			max: parseInt(jQuery(value).attr('data-end')),
			values: [
				parseInt(jQuery(value).attr('data-initial-start')),
				parseInt(jQuery(value).attr('data-initial-end'))
			],
			animate: false,
			classes: {
				"ui-slider": "slider",
				"ui-slider-range":  "slider-fill",
				"ui-slider-handle":  "slider-handle"
			},
			slide: function(event,ui) {
				var start = jQuery( event.target ).attr('data-control-start');
				jQuery( '#' + start).val(ui.values[0]);
				var end = jQuery( event.target ).attr('data-control-end');
				jQuery( '#' + end).val(ui.values[1]);				
			}
		});	

		jQuery( '#' + jQuery(value).attr('data-control-start') ).on('change', function(){			
			jQuery(value).slider( 'values', 0, jQuery(this).val() );			
		});
		
		jQuery( '#' + jQuery(value).attr('data-control-end') ).on('change', function(){			
			jQuery(value).slider( 'values', 1, jQuery(this).val() );			
		});		
		
	});

	jQuery('.x-order-cancel').click(function(event){
		
		event.preventDefault();
		var oid = jQuery(this).attr('data-oid');
		jQuery(this).html(_('Подождите...'));
		jQuery.ajax({
			url : '/plugins/sale/ajax.php',
			data: {
				action: 'order_cancel',
				id: oid
			},
			success: function (data) {                                
				jQuery('.x-order-'+oid+' .x-order-status').html(data);
				jQuery('.x-order-'+oid+' .x-order-cancel').remove();
			}
		});				
		
	});	
	
	jQuery('.x-add-to-wishlist').click(function(event){

		var pid = jQuery(this).attr('data-id');
		if (!pid) {
			var p = jQuery(this).parents('.x-product');	
			var pid = p.attr('data-id');
		}			
		var me = jQuery(this);
		jQuery.ajax({
			url : '/plugins/sale/ajax.php',
			data: {
				action: 'wishlist_add',
				id: pid
			},
			dataType: 'json',
			success: function (data) {
				jQuery(document).trigger( 'cetera.sale.wishlist.add', [ data ] );
				sale_show_result_tooltip( me, _('Товар добавлен в список') ) ;
			}
		});				
		
	});	
	
	jQuery('.x-add-to-compare').click(function(event){

		event.preventDefault();
		var pid = jQuery(this).attr('data-id');
		if (!pid) {
			var p = jQuery(this).parents('.x-product');	
			var pid = p.attr('data-id');
		}			
		var me = jQuery(this);
		jQuery.ajax({
			url : '/plugins/sale/ajax.php',
			data: {
				action: 'compare_add',
				id: pid
			},
			dataType: 'json',
			success: function (data) {
				jQuery(document).trigger( 'cetera.sale.compare.add', [ data ] );
				sale_show_result_tooltip( me, _('Товар добавлен к сравнению') ) ;
			}
		});				
		
	});	
	
	jQuery('.x-remove-from-compare').click(function(event){

		event.preventDefault();
		var pid = jQuery(this).attr('data-id');
		if (!pid) {
			var p = jQuery(this).parents('.x-product');	
			var pid = p.attr('data-id');
		}		
		var me = jQuery(this);
		jQuery.ajax({
			url : '/plugins/sale/ajax.php',
			data: {
				action: 'compare_remove',
				id: pid
			},
			dataType: 'json',
			success: function (data) {
				jQuery(document).trigger( 'cetera.sale.compare.remove', [ data ] );
				sale_show_result_tooltip( me, _('Товар удален из списка') ) ;
			}
		});				
		
	});		

	jQuery('.x-clear-compare').click(function(event){

		event.preventDefault();
		jQuery.ajax({
			url : '/plugins/sale/ajax.php',
			data: {
				action: 'compare_clear'
			},
			dataType: 'json',
			success: function () {
				jQuery(document).trigger( 'cetera.sale.compare.clear' );
			}
		});				
		
	});		

	jQuery(".x-more").click(function(event){
		event.preventDefault();
		var q = jQuery(this).parents('.x-product').find('.x-quantity');	
		var v = parseInt(q.val());
		if (isNaN(v)) v = 0;
		v = v+1;
		if (v<1) v = 1;		
		q.val(v);
	});
	
	jQuery(".x-less").click(function(event){
		event.preventDefault();
		var q = jQuery(this).parents('.x-product').find('.x-quantity');	
		var v = parseInt(q.val());
		if (isNaN(v)) v = 0;
		v = v-1;
		if (v<1) v = 1;		
		q.val(v);
	});	
	
	jQuery(".x-add-to-cart").click(function() {

		var me = jQuery(this);
		var pid = me.attr('data-id');
		var p = jQuery(this).parents('.x-product');	
		if (!pid) {
			var pid = p.attr('data-id');
		}
		if (me.attr('data-quantity')) {
			var v = parseInt(me.attr('data-quantity'));
		}
		else {
			var v = parseInt(p.find('.x-quantity').val());
		}
		if (isNaN(v)) v = 1;
		
		var data = {
			action: 'add_to_cart',
			quantity: v,
			id: pid
		}		
		
		if (me.attr('data-offer-id')) {
			data.offer_id = parseInt(me.attr('data-offer-id'));
		}
		else {
			var o = p.find('.x-offer');
			if (o.length == 1) {
				data.offer_id = p.find('.x-offer').val();
			}
			else if (o.length > 1) {
				data.offer_id = p.find('.x-offer:checked').val();
			}
		}
		
		p.find('.x-options').each(function(){
			
			if(  $(this).prop('type') == 'radio' || $(this).prop('type') == 'checkbox' ) {
				if (!$(this).prop('checked')) return;
			}	
				
			if( $(this).attr('name') && $(this).val() ) {
				if (!data.options) data.options = [];
				data.options[data.options.length] = {
					name: $(this).attr('name'),
					value: $(this).val()
				};
			}
		});
		
        jQuery.ajax({
            url : '/plugins/sale/ajax.php',
			data: data,
			dataType: 'json',
            success: function (data) {                                
				jQuery(document).trigger( 'cetera.sale.basket.add', [ data ] );	
				jQuery(document).trigger( 'cetera.sale.cart.add', [ data ] );	
				
				if (me.hasClass('x-checkout')) {
					var url = '/order/';
					if (me.attr('data-checkout-url')) {
						url = me.attr('data-checkout-url');
					}
					window.location = url;
				} else {
					sale_show_result_tooltip( me, _('Товар добавлен в корзину') ) ;
				}
            },
            error: function (xhr) {
				sale_show_result_tooltip( me, xhr.responseJSON.message );
            }			
        });		
	
		
	});	
	
	jQuery(".x-remove-from-wishlist").click(function(event) {
		event.preventDefault();
        var me = jQuery(this);
        var p = jQuery(this).parents('.x-product');	
        var pid = jQuery(this).attr('data-id');
        if (!pid) {
            var p = jQuery(this).parents('.x-product');
            var pid = p.attr('data-id');
        }        
        jQuery.ajax({
            url : '/plugins/sale/ajax.php',
			dataType: 'json',
			data: {
				action: 'wishlist_remove',
				id: pid
			},
            success: function (data) {
				jQuery(document).trigger( 'cetera.sale.wishlist.add', [ data ] );
				p.remove();
            }
        });			
	});
	
	// расчет стоимости доставки выбранным метедом
	jQuery('.x-delivery-method-calculate').click(function(event) {
		event.preventDefault();
		var me = jQuery(this);
		var form = me.parents('.x-order-form');
		var data = form.serializeArray();
		
		me.html('Подождите ...');
        jQuery.ajax({
            url : '/plugins/sale/ajax.php?action=delivery_calculate&delivery_method='+me.attr('data-id'),
			data: data,
			dataType: 'json',
			method: 'post',
			success: function (data) { 
				var p = me.parents('.x-delivery-method');
				p.find('.x-delivery-method-cost').removeClass('error').html( data.delivery_cost );
				if (p.find('input:checked').length)
				{
					jQuery('.x-delivery-cost').html(data.delivery_cost);
					jQuery('.x-total').html(data.total);
					jQuery('.x-order-confirm').removeAttr('disabled');
				}
				if (data.html)
				{
					p.find('.x-delivery-method-html').html( data.html );
				}
				
            },
			error: function(xhr) {
				me.parents('.x-delivery-method').find('.x-delivery-method-cost').addClass('error').html( xhr.responseJSON.message );
			},
            complete: function () {                                
				me.html(_('Пересчитать'));
            }
        });			
	});
	
	// очистить корзину
	jQuery('.x-sale-cart-clear').click(function(event) {
		jQuery(this).html(_('Удаляю ...'));
		Sale.Cart.clear({
			success: function(elm, data) {
				jQuery('.x-sale-cart-content').remove();
				jQuery('.x-sale-cart-empty').removeClass('hide');	
                jQuery('.x-sale-cart-clear').hide();
			},
			error: function(elm, data) {
				jQuery(elm).html(_('Что-то пошло не так'));
			},
			scope: this
		});
		return;
	});
	
	jQuery('.x-sale-cart-content .x-more').each(function(key,value){
		
		jQuery(value).click(function(){	
			var id = jQuery(this).data('id');
			var itemId = '#x-cart-item-' + id + ' ';			
			var v = parseInt(jQuery(itemId + ".x-quantity").val() );
			if (isNaN(v)) v = 0;
			v = v+1;
			if (v<1) v = 1;	
			Sale.Cart.setProductQuantity({id: id, quantity: v});
		});		
		
	});
	
	jQuery('.x-sale-cart-content .x-less').each(function(key,value){
		
		jQuery(value).click(function(){	
			var id = jQuery(this).data('id');
			var itemId = '#x-cart-item-' + id + ' ';		
			var v = parseInt(jQuery(itemId + ".x-quantity").val() );
			if (isNaN(v)) v = 0;
			v = v-1;
			if (v<1) v = 1;			
			Sale.Cart.setProductQuantity({id: id, quantity: v});
		});		
		
	});	
	
	jQuery('.x-sale-cart-content .x-quantity').each(function(key,value){
		
		jQuery(value).on('input', function(){	
			var id = jQuery(this).data('id');
			var itemId = '#x-cart-item-' + id + ' ';		
			var v = parseInt( jQuery(itemId + ".x-quantity").val() );
			if (isNaN(v)) v = 1;
			if (v<1) v = 1;				
			Sale.Cart.setProductQuantity({id: id, quantity: v});
		});		
		
	});	
	
	jQuery('.x-sale-cart-content .x-delete').each(function(key,value){
		
		jQuery(value).click(function(e){	
			var id = jQuery(this).data('id');
			Sale.Cart.setProductQuantity({id: id, quantity: 0});
			e.preventDefault();
		});		
		
	});

	jQuery(".x-sale-cart-item .x-sale-cart-option").on('change', function(){
		
		jQuery.ajax({
			url : '/plugins/sale/ajax.php',
			data: {
				action: 'set_cart_option',
				id    : jQuery(this).data('id'),
				option_name: jQuery(this).attr('name'),
				option_value: jQuery(this).val()
			},
			dataType: 'json'					
		});	

	});	

});

function sale_show_result_tooltip( elm, text ) {
    if (!elm.foundation) return;
	var tt = new Foundation.Tooltip(elm,{
		 clickOpen: false,
		 disableHover: true,
		 tipText: text
	});
	elm.foundation('show');
	setTimeout(function(){
		elm.foundation('hide');
		elm.foundation('destroy');
	}, 2000);	
}

function sale_order_get_available_payments( form ) {

	jQuery.ajax({
		url : '/plugins/sale/ajax.php?action=get_payment_methods',
		data: form.serializeArray(),
		dataType: 'json',
		method: 'post',
		success: function (data) {                                
			jQuery('.x-total').html(data.total);
			jQuery('.x-delivery-cost').html(data.delivery_cost);
			jQuery('.x-delivery-'+data.delivery_id+' .x-delivery-method-cost').html(data.delivery_cost);
			jQuery('.x-payment-method').addClass('hide');
			for (var i = 0; i  < data.payment.length; i++) 
			{
				jQuery(".x-payment-"+data.payment[i].id).removeClass('hide');
			}		
			jQuery('.x-delivery-method').addClass('hide');
			for (var i = 0; i  < data.delivery.length; i++) 
			{
				jQuery(".x-delivery-"+data.delivery[i].id).removeClass('hide');
			}
			if (data.total) {
				form.attr('data-payment-ok', 1);
			}
			else {
				form.attr('data-payment-ok', 0);
			}
			sale_order_check_form( form );
		}
	});		

}

function sale_order_check_form( form ) {
	
	form.find('.x-order-error').html('');
	
	valid = parseInt(form.attr('data-payment-ok')) > 0;
	
	form.find('input[required]').each(function(){
		
		if (
				(jQuery(this).prop('type') == 'checkbox' && !jQuery(this).prop('checked'))
				||
				(jQuery(this).prop('type') != 'checkbox' && !jQuery(this).val())
		   ) {
			form.find('.x-order-error').html(_('Заполните обязательные поля.'));
			valid = false;
			return false;
		}
		
	});
	
	if (valid) {
		form.find('.x-order-confirm').removeAttr('disabled');
	}
	else {
		form.find('.x-order-confirm').attr('disabled', 1);
	}	
	
}

function sale_qd () {
	var qd = {};
	location.search.substr(1).split("&").forEach(function(item) {
		var s = item.split("="),
			k = s[0] && decodeURIComponent(s[0]),
			v = s[1] && decodeURIComponent(s[1]);
		qd[k] = v;

	});
	return qd;
}

function saleClearFilter(elm) {
	elm.find('input[type="text"],input[type="number"]').each(function(){
		jQuery(this).val( jQuery(this).attr('data-default') ).trigger('change');
	});	
	elm.find('input:checked').prop('checked', false);
	elm.find('select').val('');
	saleCheckFilter(elm);
}
function saleCheckFilter(elm) {
	var c = elm.find('.x-clear');
	if (elm.find('input[type="checkbox"], input[type="radio"]').length>0)
	{
		c.toggle(  elm.find(':checked').length > 0 );
	}
	if (elm.find('select').length>0)
	{
		c.toggle( elm.find('select').val() != '' );
	}
	if (elm.find('input[type="text"]').length>0)
	{
		var f = false;
		elm.find('input[type="text"]').each(function(){
			if (jQuery(this).val() != jQuery(this).attr('data-default')) f = true;
		});
		c.toggle( f );
	}			
}

var Sale = Sale || {};
(function() {
	
	Sale.Cart = {
		
		// очистить корзину
		// options.success - callback успешного завершения
		// options.error - callback ошибки
		// options.scope - scope объект для callback
		clear: function(options) {

			jQuery.ajax({
				url : '/plugins/sale/ajax.php?action=cart_clear',
				dataType: 'json',
				success: function(data) {
					jQuery(document).trigger( 'cetera.sale.basket.add', [ data ] );	
					if (options.success) options.success.call(options.scope, data);
				},
				error: function(data) {
					if (options.error) options.error.call(options.scope, data);
				}
			});		
		
        },
		
		// изменить кол-во товара в корзине
		// options.id - id товара
		// options.quantity - кол-во товара
		// options.success - callback успешного завершения
		// options.error - callback ошибки
		// options.scope - scope объект для callback		
		setProductQuantity: function(options) {
			if (!options.id) {
				console.log('Product id required');
				return false;
			}
			var itemId = '#x-cart-item-' + options.id + ' ';
			var q = jQuery(itemId + ".x-quantity");	
			if (q) q.val( options.quantity );
			
			var s = jQuery(itemId + ".x-sum");
			if (s) {
				var s_backup = s.html();
				s.html(_('Подождите...'));
			}
		
			var sum = jQuery(".x-total-sum");
			if (sum) {
				var sum_backup = sum.html();
				sum.html(_('Подождите...'));
			}
			var sumFull = jQuery(".x-total-full");
			if (sumFull) {
				var sumFull_backup = sumFull.html();
				sumFull.html(_('Подождите...'));
			}            
			var discount = jQuery(".x-total-discount");
			if (discount) {
				var discount_backup = discount.html();
				discount.html(_('Подождите...'));
			}            
			
			jQuery.ajax({
				url : '/plugins/sale/ajax.php?action=set_cart_quantity&id='+options.id+'&quantity='+options.quantity,
				dataType: 'json',
				success: function(data) {
					jQuery(document).trigger( 'cetera.sale.basket.add', [ data ] );	
					if (sum) sum.html(data.total_display);
                    if (sumFull) sumFull.html(data.total_full_display);
					if (s) s.html(data.sum);
                    jQuery(".x-total-count").html(data.count);
                    if (discount) discount.html(data.total_discount);
					jQuery(".x-total-quantity").html( data.count + ' '+_('шт.') );
					if (options.quantity == 0) {
						jQuery(itemId).remove();										
					}
					if (options.success) options.success.call(options.scope, data);					
				},
				error: function(data) {
					jQuery(itemId + ".x-quantity").val( jQuery(itemId + ".x-quantity").attr('data-backup') );
					if (sum) sum.html(sum_backup);
                    if (sumFull) sumFull.html(sumFull_backup);
					if (s) s.html(s_backup);
                    if (discount) discount.html(discount_backup);
					if (options.error) options.error.call(options.scope, data);
				}
			});			
		}
    };
	
}());