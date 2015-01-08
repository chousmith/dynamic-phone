/** admin.js **/

jQuery(function($){

	var dynamic_phone_max_row = $('#dynamic_phone_table tr').length - 1;
	$('#dynamic_phone_table tr').each(function( index ){
		if ( index !== dynamic_phone_max_row ) {
			$(this).find('td:last-child a').remove();
		}
	});
  var dynamic_phone_add_click = function(){
		var n = parseInt( $(this).attr('rel') ) + 1;
		var m = n + 1;
		$('#dynamic_phone_table tbody').append('<tr><td><input type="text" size="8" name="dynamic_phone[numbers][query_string_value]['+ n +']" /></td><td><input type="text" size="8" name="dynamic_phone[numbers][form_field_value]['+ n +']" /></td><td><input type="text" size="8" name="dynamic_phone[numbers][cookie_value]['+ n +']" /></td><td><input type="tel" size="20" name="dynamic_phone[numbers][dynamic_phone_number]['+ n +']" /></td><td><a class="phone-row-more" rel="'+ m +'" style="cursor: pointer;">+ add row</a></td></tr>')
      .find('a.phone-row-more').bind('click', dynamic_phone_add_click);
		$(this).remove();
	};
  
	$('a.phone-row-more').bind('click', dynamic_phone_add_click);

	$('input[p-label], select[p-label], textarea[p-label]').each(function(){
		var mark = $(this).attr('p-label');
		$(this).watermark(mark);
	});
});