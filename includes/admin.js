/** admin.js **/

jQuery(function($){

	var row_count = $('#dynamic_phone_table tr').length;
	$('#dynamic_phone_table tr').each(function( index ){
		if ( index !== row_count - 1) {
			$(this).find('td:last-child a').remove();
		}
	});

	$('a.phone-row-more').bind('click', function(){
		var n = parseInt( $(this).attr('rel') ) + 1;
		var m = n + 1;
		$('#dynamic_phone_table tbody').append('<tr><td><input type="text" size="8" name="nlk_dynamic_phone[numbers][query_string_value]['+ n +']" /></td><td><input type="text" size="8" name="nlk_dynamic_phone[numbers][form_field_value]['+ n +']" /></td><td><input type="text" size="8" name="nlk_dynamic_phone[numbers][cookie_value]['+ n +']" /></td><td><input type="tel" size="20" name="nlk_dynamic_phone[numbers][dynamic_phone_number]['+ n +']" /></td><td><a class="phone-row-more" rel="'+ m +'" style="cursor: pointer;">+ add row</a></td></tr>');
		$(this).remove();
	});


	$('input[p-label], select[p-label], textarea[p-label]').each(function(){
		var mark = $(this).attr('p-label');
		$(this).watermark(mark);
	});
});