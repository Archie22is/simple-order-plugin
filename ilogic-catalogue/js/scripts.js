
$(document).ready(function() {
	
	//$('.side_categories .parent').click(function(){
	//	$('.child_'+$(this).attr('id')).stop(true, true).fadeIn('slow');;
	//});
	
	//$('.side_categories .parent').mouseleave(function(){
	//	$('.child_'+$(this).attr('id')).stop(true, true).fadeOut('slow');;
	//s});
	
	$('.cat_dropdown').click(function(){
		window.location = 'http://formforce.co.za/products/';
	});
	
	$('#menu-item-367').hover(function(){
	
		$('.cat_dropdown').stop(true, true).fadeIn('fast');
	});
	

	
	$('.cat_dropdown').mouseenter(function(){
		$(this).stop(true, true).fadeIn('fast');
	})
	
	$('.cat_dropdown').mouseleave(function(){
		$(this).stop(true, true).fadeOut('fast');

	})
	
	$('.cat_dropdown div').mouseenter(function(){
		$(this).children('.child_categories').stop(true, true).fadeIn('fast');
	})
	
	$('.cat_dropdown div').mouseleave(function(){
		$(this).children('.child_categories').stop(true, true).fadeOut('fast');
	})
	
	
	$(".fancybox").fancybox();
	
	$('.product_add_to_cart').click(function(){	
		
		var obj_ = $(this);
		if($('.td_right').html() == null){
			if( obj_.parent('li').children('.product_error_con_page').html() != null){
				obj_.parent('li').children('.product_error_con_page').remove();
			}
		}else{
			if( $('.product_error_con_page').html() != null){
				$('.product_error_con_page').remove();
			}

		}
		var error_html = '<div class="product_error_con_page">There is insufficient stock to fill this order. Please try a lower quantity.<div class="img"></div></div>';
		var amount_ = $(this).parent('li').children('.product_quantity_container').children('.product_input').val() ;
		
		if(amount_ == null){
			amount_ = $('.product_input').val() ;
		}
		var variants_ = '';
		var variants_con_obj = obj_.parent('li').children('.products_variants_container');
		var variants_con_obj_2 = obj_.parent('td').parent('tr').children('.td_right').children('.variants').children('.products_variants_container');
		if( variants_con_obj.html() != null){
			variants_con_obj.children('select').each(function(index) {
				if($(this).val() != ""){
					variants_ += 'variant['+$(this).attr('name')+']='+$(this).val()+'&';
				}
			});
		}else if(variants_con_obj_2.html() != null){
			variants_con_obj_2.children('select').each(function(index) {
				if($(this).val() != ""){
					variants_ += 'variant['+$(this).attr('name')+']='+$(this).val()+'&';
				}
			});
		}
		
		$.post(wp_ajax.ajaxurl, { action: 'add_product', product_id: $(this).attr('id'), amount: amount_ , variants: variants_}, function(data){	
				if(!data.available){
					if($('.td_right').html() == null){
					obj_.before(error_html);
					}else{
						$('.product_quantity_container').after(error_html);
					}					
				}else{
					show_cart(data.cart)
				}
				
			}
			, 'json'
			);	
	});
});

function load_products(){
	$.post(wp_ajax.ajaxurl, { action: 'load_products' }, function(data){	
					show_cart(data.cart)
				
			}, 'json');	
}

function remove_product(product_id){
	$.post(wp_ajax.ajaxurl, { action: 'remove_product', product_id: product_id}, function(data){		
		if($('#product_row_'+product_id).next('.product_error_col') != null){//remove the error box
			$('#product_row_'+product_id).next('.product_error_col').fadeOut('fast', function(){
				$(this).remove();	
			})
		}					
		$('#product_row_'+product_id).fadeOut('fast', function(){
			$(this).remove();
		});				
	}, 'json');		
}

function close_cart(){
	$('#light_box_bg').fadeOut('fast', function(){ $(this).remove() })
	$('#cart_container').fadeOut('fast', function(){ $(this).remove() })
}

function checkInputs2(field, rules, i, options){
                if (field.val() == "Type your company name here" || field.val() == "Type your name here" || field.val() == "Type your number here" || field.val() == "Type your email address here") {
                    // this allows to use i18 for the error msgs
                    return '* This field is required';
                }
}

function show_cart(products){
	var cart_html = '';
	var send_html = '<div class="send_block">';
	
	send_html += '<form id="send_invoice"><div class="send_title">SEND MY INVOICE</div>';
	send_html += '<input class="validate[required,funcCall[checkInputs2]] input invoice_input" type="text" value="Type your company name here" id="company" onfocus="if(this.value == \'Type your company name here\'){this.value = \'\'};" onblur="if(this.value == \'\'){this.value = \'Type your company name here\'};" />';
	send_html += '<input class="validate[required,funcCall[checkInputs2]] input invoice_input" type="text" value="Type your name here" id="name" onfocus="if(this.value == \'Type your name here\'){this.value = \'\'};" onblur="if(this.value == \'\'){this.value = \'Type your name here\'};" />';
	send_html += '<input class="validate[required,funcCall[checkInputs2]] input invoice_input" type="text" value="Type your number here" id="number" onfocus="if(this.value == \'Type your number here\'){this.value = \'\'};" onblur="if(this.value == \'\'){this.value = \'Type your number here\'};" />';
	send_html += '<input class="validate[required, custom[email], funcCall[checkInputs2]] input invoice_input" type="text" value="Type your email address here" id="email" onfocus="if(this.value == \'Type your email address here\'){this.value = \'\'};" onblur="if(this.value == \'\'){this.value = \'Type your email address here\'};" />';
	send_html += '<input type="button" value="" class="invoice_send" onclick="validate_all()" />';	
	send_html += '</div></form>';
	 
	$('body').append('<div id="light_box_bg"></div>');
	$('#light_box_bg').fadeIn('slow');
	
	cart_html += '<div class="products_scroll"><table class="cart">';
	
	cart_html += '<tr id="cart_title"><th colspan="4">MY ORDER</th></tr>';
	
	cart_html += '<tr id="cart_title_row">';
	cart_html += 	'<th>Category</th>';
	cart_html += 	'<th>Name</th>';
	cart_html += 	'<th>Quantity</th>';
	cart_html += 	'<th>Variants</th>';
	cart_html += '</tr>';
	cart_html += '<tr class="line"><td colspan="4"><hr /></td></tr>';
		
	for (var key in products) {
		var variants_html = '';
		for (var key_var in products[key].variants) {
			var variant_obj = products[key].variants[key_var];
			variants_html += '<div class="variants_last_inner_con" ><font>'+variant_obj.parent+':</font> '+variant_obj.child+'</div>';
		}
		cart_html += '<tr id="product_row_'+key+'" class="product_row">';
		cart_html += 	'<td>'+products[key].category+'</td>';
		cart_html += 	'<td>'+products[key].name+'</td>';
		cart_html += 	'<td class="in_here"><input type="text" value="'+products[key].amount+'" class="product_amount_mod" id="'+key+'" onchange="validate_amount(\''+key+'\', this.value);" /><div class="remove_product" onclick="remove_product(\''+key+'\')"></div></td>';
		cart_html += 	'<td class="variants_last_con">'+variants_html+'</td>';
		cart_html += '</tr>';
	}
	cart_html += '</table></div>';
	$('body').append('<div id="cart_container">'+cart_html+send_html+'<div class="continue" onclick="close_cart()"></div><div class="close_cart" onclick="close_cart()">CLOSE</div></div>');
	$('#cart_container').fadeIn('fast');
	$("#send_invoice").validationEngine({scroll: false});	
	
}

function validate_all(){
	
	var thanks_html = '';
	
	thanks_html += '<div class="products_scroll"><table class="cart">';
	thanks_html += '<tr id="cart_title"><th colspan="3">MY ORDER</th></tr>';
	thanks_html += 	'<tr id="cart_title_row"><th>YOUR ORDER HAS BEEN SENT THROUGH TO:</th></tr>';
	thanks_html += '<tr class="line"><td colspan="3"><hr /></td></tr>';
	thanks_html += '<tr class="product_row"><td>"'+$('.invoice_input#email').val()+'"</td></tr>';
	thanks_html += '<tr class="line"><td colspan="3"><hr /></td></tr>';	
	thanks_html += 	'<tr id="cart_title_row"><th>Thank-you! We look forward to your business.</th></tr>';	
	thanks_html += '<tr class="product_row"><td><div class="order_close" onclick="close_cart()"></div></td></tr>';	
	thanks_html += '</table></div>';
	
	

	if($("#send_invoice").validationEngine('validate')){
		if($('.invoice_input#company').val() != '' && $('.invoice_input#name').val() != '' && $('.invoice_input#number').val() != '' && $('.invoice_input#email').val() != ''){
			
			if($('.invoice_input#company').val() != 'Type your company name here' && $('.invoice_input#name').val() != 'Type your name here' && $('.invoice_input#number').val() != 'Type your number here' && $('.invoice_input#email').val() != 'Type your email address here'){
			$.post(wp_ajax.ajaxurl, { 
				action: 'send_catalogue', 
				company: $('.invoice_input#company').val(),
				name: $('.invoice_input#name').val(),
				number: $('.invoice_input#number').val(),
				email: $('.invoice_input#email').val()
				}, function(data){		
				$('.products_scroll').fadeOut('fast');
				$('.send_block').fadeOut('fast', function(){
					$('.products_scroll').remove();
					$('.send_block').remove();
					$('#cart_container').append(thanks_html);
				});
			}, 'json');	
		}
		}
	}
}

function validate_amount_2(product_id, amount){
	var return_ = 1;	
	$.ajax({
      url: wp_ajax.ajaxurl, 
      type: 'POST',
      async: false,
      cache: false,
	  dataType: 'json',
      data: { action: 'check_product', product_id: product_id, amount: amount},
	  success: function(data) {
		  
		  if($('#product_row_'+product_id).next('.product_error_col') != null){//remove the error box
				$('#product_row_'+product_id).next('.product_error_col').fadeOut('fast', function(){
					$(this).remove();	
				})
			}				
			if(!data.available){
				$('#product_row_'+product_id).after('<tr class="product_error_col"><td colspan="3"><div class="product_error_con">There is insufficient stock to fill this order. Please try a lower quantity.<div class="img"></div></div></td></tr>');
				return_ = 0;
			}
 
      }

   });
   
   return return_;

}

function validate_amount(product_id, amount){

	return_ = 1;	
	$.ajax({
      url: wp_ajax.ajaxurl, 
      type: 'POST',
      async: true,
      cache: false,
	  dataType: 'json',
      data: { action: 'check_product', product_id: product_id, amount: amount},
	  success: function(data) {
		  
		  if($('#product_row_'+product_id).next('.product_error_col') != null){//remove the error box
				$('#product_row_'+product_id).next('.product_error_col').fadeOut('fast', function(){
					$(this).remove();	
				})
			}				
			if(!data.available){
				$('#product_row_'+product_id).after('<tr class="product_error_col"><td colspan="3"><div class="product_error_con">There is insufficient stock to fill this order. Please try a lower quantity.<div class="img"></div></div></td></tr>');
				return_ = 0;
			}
 
      }

   });
   
   return return_;

}
