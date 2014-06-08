jQuery(document).ready(function($) { /* Draft function */
	$('#ois_save_draft').click(function() {
		$('#newskin_status').val('draft');
	});
	
	$('#ois_excl_cat').click(function (e) {
  	e.preventDefault();
	  if ($('#ois_select_cat').val() !== 'Select from all categories') {
			
			var cur_cats = $('#ois_exclude_cats').val();
			
			if (cur_cats !== '') {
				cur_cats = cur_cats + ',' + $('#ois_select_cat').val();
			} else {
				cur_cats = $('#ois_select_cat').val();
			} // else
			
			$('#ois_exclude_cats').val(cur_cats);
		} // if
  }); // .click
  
  $('#ois_excl_post').click(function (e) {
  	e.preventDefault();
	  if ($('#ois_select_post').val() !== 'Select from all posts') {
			var cur_posts = $('#ois_exclude_posts').val();
			if (cur_posts !== '') {
				cur_posts = cur_posts + ',' + $('#ois_select_post').val();
			} else {
				cur_posts = $('#ois_select_post').val();
			} // else
			
			$('#ois_exclude_posts').val(cur_posts);
		} // if
  }); // .click
/*
	Do all the nice little things for input validation.
*/
	$('.ois_optin_account_input').blur(function() {
		var number = $(this).attr('number');
		var account = $(this).attr('account');
		var specific = $(this).attr('specific');
		if ($(this).val().trim() != '') {
			$(this).removeClass('ois_textbox_error');
			$(this).addClass('ois_textbox_approve');
			$('#ois_account_approve_' + number + '_' + account + '_' + specific).show();
			$('#ois_account_disapprove_' + number + '_' + account + '_' + specific);
		} else {
			$(this).addClass('ois_textbox_error');
			$(this).removeClass('ois_textbox_approve');
			$('#ois_account_disapprove_' + number + '_' + account + '_' + specific).show();
			$('#ois_account_approve_' + number + '_' + account + '_' + specific).hide();
		}
	});
	$('#ois_skin_name').blur(function() {
		if ($(this).val().length == 1) {
			$('#ois_name_approve').hide();
			$('#ois_name_disapprove').text('May I suggest you be a tad more descriptive?');
			$('#ois_name_disapprove').show();
			$(this).addClass('ois_textbox_error');
			$(this).removeClass('ois_textbox_approve');
		} else if ($(this).val().trim() != '') {
			$('#ois_name_disapprove').hide();
			$('#ois_name_approve').show();
			$(this).removeClass('ois_textbox_error');
			$(this).addClass('ois_textbox_approve');
		} else {
			$('#ois_name_approve').hide();
			$('#ois_name_disapprove').show();
			$(this).removeClass('ois_textbox_approve');
			$(this).addClass('ois_textbox_error');
		}
	});
	$('.ois_textbox').focus(function() {
		$(this).parent().parent().css({
			'background-color': '#f7f7f7'
		});
		if ($(this).attr('id') != 'ois_skin_name' && $('#ois_skin_name').val().trim() == '') {
			$('#ois_name_approve').hide();
			$('#ois_skin_name').addClass('ois_textbox_error');
			$('#ois_name_disapprove').show();
		} else {
			$('#ois_name_disapprove').hide();
		}
	});
	$('.ois_textbox').blur(function() {
		$(this).parent().parent().css({
			'background-color': '#f9f9f9',
			'box-shadow': 'none'
		});
	});
	$('#new_skin_description').blur(function() {
		if ($(this).val().trim() != '') {
			$('#ois_description_approve').show();
			$(this).addClass('ois_textbox_approve');
		} else {
			$('#ois_description_approve').hide();
			$(this).removeClass('ois_textbox_approve');
		}
	});
	$('.ois_optin_choice').change(function() {
		$('.ois_optin_account').hide();
		$('.ois_optin_' + $(this).val()).show();
	});
/*
	Minimization for the headers
*/
	$('.ois_header_min').click(function() {
		var self = $(this);
		$(this).parent().parent().parent().parent().parent().find('tr').each(function() {
			if ($(this).attr('class') != 'ois_minimized_row') {
				var closeUrl = self.attr('data-closed');
				self.html('<img src="' + closeUrl + '" style="height:25px;margin-bottom:-5px;" />');
				if ($(this).attr('class') != 'ois_header_row') {
					$(this).slideUp('slow');
					$(this).attr('class', 'ois_minimized_row');
				}
			} else {
				self.text('Minimize');
				var openUrl = self.attr('data-open');
				self.html('<img src="' + openUrl + '" style="height:25px;margin-bottom:-5px;" />');
				$(this).slideDown('slow');
				$(this).attr('class', '');
			}
		});
	});
});