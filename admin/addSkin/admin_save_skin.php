<?php
/*
	Function: ois_handle_new_skin()
	Description: Compiles all data for a new skin, and saves them to the Wordpress database.
	Precondition: none.
	Postconditions: 
		1. The new skin data is saved to the database.
		2. A static html file, and static css file, are created on the server.
		3. User is redirected to stats page if new skin is created.
*/
function ois_handle_new_skin() 
{
	if ( empty($_POST) || !check_admin_referer('ois_add_field', 'save_data') ) 
	{
		// There was some kind of error.
		print 'Sorry, there has been an error. Please return to your dashboard before trying again.';
		exit;
	} // if
	else 
	{
		/** SAVE SKIN SETTINGS **/
		/* 	Because it makes me nervous to work with the $_POST variable in PHP - 
			Given that it is illegal to modify this variable, and there are many 
			confusing mutator functions in PHP - I will assign it to another variable. */
		$posted_items = $_POST;

		/* DESCRIPTIVE SETTINGS*/
		$skin_data = array();
		// Get Skin Name; used in the menu item
		if (trim($posted_items['newskin_name']) == '') {
			$skin_data['title'] = htmlentities('Untitled Skin');
		} // if
		else 
		{
			$skin_data['title'] = $posted_items['newskin_name'];
		} // else
		// Get Skin Description.
		//$skin_data['description'] = $posted_items['newskin_description'];
		// Get Skin Status; publish, draft, trash
		$skin_data['status'] = $posted_items['newskin_status'];
		// Get Skin Design - the id of the design used
		$skin_data['design'] = $posted_items['design'];
		// Get Skin Optin Choice; e.g. feedburner, mailchimp, etc.
		$optin_choice = $posted_items['newskin_optin_choice']; // used again later.
		$skin_data['optin-service'] = $optin_choice;
		$skin_data['last_modified'] = date('Y-m-d H:i:s');
		
		/* MISC SETTINGS - E.G., POSITION ON PAGE */
		/*
			We have some variables that we might expected to be passed,
			but if these are not added, they might be null (in which case things break.)
			So, we can make an array of these "expected properties", and go through the
			posted array, looking for these properties, and saving them if found.
			
			Moreover, these specific variables will be passed directed to storage, without changes.
			E.g. exclude_posts falls under this category, but not exclude_categories, 
			which is a <select> input.
		*/
		$expected_properties = array(
			/* POSITION AND FADING */
			'post_position',
			'below_x_paragraphs',
			'scrolled_past',
			'special_fade', // yes or no.
			'skin_center', // center or nothing
			/* MARGINS */
			'margin_type',
			/* SPLIT TESTING */
			'split_testing', // is this in a split testing group?
			/* EXCLUDE POSTS  */
			'exclude_posts',
			/* PLACEHOLDER TYPE */
			'placeholder_type', // javascript or HTML5?
			'disable_mobile',
		); // expected skin data
		
		foreach ($expected_properties as $expected_name)
		{
			if (isset($posted_items[$expected_name]))
			{
				$skin_data[$expected_name] = $posted_items[$expected_name];
			} // if
		} // foreach
		
		/* MARGINS */
		$margins = array (
			'top' => $posted_items['margin_top'],
			'bottom' => $posted_items['margin_bottom'],
		); // margins array
		
		// If the skin is centered, then the left and right margins are "auto"
		if (!empty($posted_items['skin_center']) && $posted_items['skin_center'] == 'center') {
  	  $margins['left'] = 'auto';	
  	  $margins['right'] = 'auto';
		} else {
		  // Otherwise, they are whatever the user sets them to be.
		  $margins['left'] = $posted_items['margin_left'];
			$margins['right'] = $posted_items['margin_right'];
		}
		$skin_data['margins'] = $margins;
		
		/* OPTIN FORM */
		$optin_options = array(); // holds the settings for the optin form.
		// Get the opint information.
		// Could certainly use a switch-case for the process below.
		if ($optin_choice == 'feedburner') {
			// feedburner
			$feedburner_id = $posted_items['feedburner-id'];
			update_option('ois_feedburner_id', $feedburner_id);
			$optin_options['feedburner_id'] = $feedburner_id;
		} // if
		else if ($optin_choice == 'aweber' && !empty($posted_items['aweber-id'])) {
			// aweber
			$aweber_id = $posted_items['aweber-id'];
			update_option('ois_aweber_id', $aweber_id);
			$optin_options['aweber_id'] = $aweber_id;

		} // else if
		else if ($optin_choice == 'mailchimp') 
		{
		  $type = $posted_items['mailchimp_type'];
		  $optin_options['mailchimp_type'] = $type;
		  
			// Mailchimp
			if ($type == 'api' &&  isset($posted_items['mailchimp_api_key']) && 
			      isset($posted_items['mailchimp_list_id'])) {
			      
        // The user has entered API details. Includes key and list ID
        // Get key
        $key = $posted_items['mailchimp_api_key'];
        $optin_options['mailchimp_api_key'] = $key;
        update_option('ois_mailchimp_api_key', $key);
        // Get List ID
        $list = $posted_items['mailchimp_list_id'];
  			$optin_options['mailchimp_list_id'] = $list;
  			update_option('ois_mailchimp_list_id', $list);
  			
  			// Because it uses the AJAX API, no need for the form action.
  			$optin_options['mailchimp_action'] = '#';
  			  
			} // if
			else if ($type == 'code' && isset($posted_items['newskin_mailchimp-form']))
			{
				$mailchimp_form = stripslashes($posted_items['newskin_mailchimp-form']);
				$mailchimp_action = explode('action="', $mailchimp_form);
				
				if (count($mailchimp_action) > 1) 
				{
					$mailchimp_action = explode('"', $mailchimp_action[1]);
					$mailchimp_action = $mailchimp_action[0];
				}  // if
				else 
				{
					$mailchimp_action = '';
				} // else
				
				// Update the settings
				$optin_options['mailchimp_action'] = $mailchimp_action;
				$optin_options['ois_mailchimp_form'] = $mailchimp_form;
				update_option('ois_mailchimp_form', $mailchimp_form);
			} // if
			
		} // else if mailchimp
		else if ($optin_choice == 'icontact') 
		{
			$icontact_form = stripslashes($posted_items['newskin_icontact-html']);
			$icontact_id = explode('name="listid" value="', $icontact_form);
			
			// Contact ID
			if (!empty($icontact_id)) {
				$icontact_id = explode('"', $icontact_id[1]);
				$icontact_id = $icontact_id[0];
				$optin_options['icontact_id'] = $icontact_id;
			}
			
			// "Special" ID
			$icontact_specialid = explode('name="specialid:' . $icontact_id . '" value="', $icontact_form);
			if (!empty($icontact_specialid)) {
				$icontact_specialid = explode('"', $icontact_specialid[1]);
				$icontact_specialid = $icontact_specialid[0];
				$optin_options['icontact_special'] = $icontact_specialid;
			} // if
			
			// Client
			$icontact_client = explode('name="clientid" value="', $icontact_form);
			if (!empty($icontact_client)) 
			{
				$icontact_client = explode('"', $icontact_client[1]);
				$icontact_client = $icontact_client[0];
				$optin_options['icontact_client'] = $icontact_client;
			} // if
			
			// Form ID
			$icontact_formid = explode('name="formid" value="', $icontact_form);
			if (!empty($icontact_formid)) 
			{
				$icontact_formid = explode('"', $icontact_formid[1]);
				$icontact_formid = $icontact_formid[0];
				$optin_options['icontact_formid'] = $icontact_formid;
			} // if
			
			// Real ID
			$icontact_real = explode('name="reallistid" value="', $icontact_form);
			if (!empty($icontact_real)) 
			{
				$icontact_real = explode('"', $icontact_real[1]);
				$icontact_real = $icontact_real[0];
				$optin_options['icontact_real'] = $icontact_real;
			} // if
			
			$icontact_double = explode('name="doubleopt" value="', $icontact_form);
			if (!empty($icontact_double)) {
				$icontact_double = explode('"', $icontact_double[1]);
				$icontact_double = $icontact_double[0];
				$optin_options['icontact_double'] = $icontact_double;
			} // if
			
			update_option('ois_icontact_html', $icontact_form);

		} 
		else if ($optin_choice == 'getResponse') 
		{
			// Get Response HTML Form
			if (!empty($posted_items['getResponse-html']) 
				&& trim($posted_items['getResponse-html']) != '') 
			{
				$gr_form = stripslashes($posted_items['getResponse-html']);
				$optin_options['getResponse_html'] = $gr_form;
				update_option('ois_getResponse_html', $gr_form);
			} // if
			
			// Get Response ID
			if (!empty($posted_items['getResponse-id']) &&
					trim($posted_items['getResponse-id']) != '') 
			{
				// The user has specified the ID.
				$gr_id = $posted_items['getResponse-id'];
			} // if
			else if (!empty($gr_form) && trim($gr_form) != '') 
			{
				// The user has given the raw HTML form code instead.
				// Extract ID.	
				$gr_bit = explode('<input type="hidden" name="webform_id" value="', $gr_form);
				
				if (!empty($gr_bit)) 
				{
					
					$gr_bit = explode('"', $gr_bit[1]);
					
					if (!empty($gr_bit)) {
						$gr_id = $gr_bit[0];
					} // if
					else 
					{
						$gr_id = '';
					} // else
				} // if
			} // else if
			else 
			{
				$gr_id = '';
			}
			$optin_options['getResponse_id'] = $gr_id;
			update_option('ois_getResponse_id', $gr_id);
		} // else if
		else if ($optin_choice == 'infusionSoft') 
		{
			// InfusionSoft HTML Form
			if (!empty($posted_items['newskin_infusionSoft-html']) 
				&& trim($posted_items['newskin_infusionSoft-html']) != '') {
				$is_form = stripslashes($posted_items['newskin_infusionSoft-html']);
				$optin_options['infusionSoft_html'] = $is_form;
				update_option('ois_infusionSoft_html', $is_form);
				
				// Extract InfusionSoft Action
				$is_bit = explode('action="', $is_form);
				if (!empty($is_bit)) 
				{
					$is_bit = explode('"', $is_bit[1]);
					if (!empty($is_bit)) 
					{
						$is_action = $is_bit[0];
						$optin_options['infusionSoft_action'] = $is_action;
						update_option('ois_infusionSoft_action', $is_action);
					} // if
				} // if			
			
				// Extract InfusionSoft ID
				$is_bit2 = explode('name="inf_form_xid" type="hidden" value="', $is_form);
				if (count($is_bit2) > 1) 
				{
					$is_bit2 = explode('"', $is_bit2[1]);
					if (!empty($is_bit2)) 
					{
						$is_id = $is_bit2[0];
						$optin_options['infusionSoft_id'] = $is_id;
						update_option('ois_infusionSoft_id', $is_id);
					} // if
				} // if
				
				// InfusionSoft Name
				$is_bit3 = explode('name="inf_form_name" type="hidden" value="', $is_form);
				if (!empty($is_bit)) 
				{
					$is_bit3 = explode('"', $is_bit3[1]);
					if (!empty($is_bit3)) 
					{
						$is_name = $is_bit3[0];
						$optin_options['infusionSoft_name'] = $is_name;
						update_option('ois_infusionSoft_name', $is_action);
					} // if
				} // if
			} // if html form
		} // else if infusionSoft
		else if ($optin_choice == 'other') 
		{
			$other_html = $posted_items['newskin_other-html'];
			$optin_options['newskin_other-html'] = $other_html;
			
			// Add this form instead of our default design's form.
			$posted_items['template-code'] = 
				str_replace('{{optin_form}}', $other_html, urldecode($posted_items['template-code']));
			update_option('ois_other_html', $other_html);
		} // else if other
		else if ($optin_choice == 'custom') 
		{
			$optin_options['custom_action'] = $posted_items['custom_action'];
			$optin_options['custom_name'] = $posted_items['custom_name'];
			$optin_options['custom_email'] = $posted_items['custom_email'];
		} // else if custom
		
		/* REDIRECT URL */
		$redirect_name = 'redirect_url_' . $optin_choice;
		if (isset($posted_items[$redirect_name])) {
			$optin_options['redirect_url'] = $posted_items[$redirect_name];
		} // if
		
			
		/* HIDDEN VALUES */
		// There are five
		$hidden = array();
		for ($i = 1; $i <= 5; $i++) // 5 inclusive, starting at 1
		{
			if (!empty($posted_items['newskin_hidden_name_' . $i])
				&& trim($posted_items['newskin_hidden_name_' . $i]) != '')
			{
				if (!empty($posted_items['newskin_hidden_value_' . $i]))
				{
					$h_val = $posted_items['newskin_hidden_value_' . $i];
				} // if
				else
				{
					// Can one have a required hidden name, with no value?
					$h_val = '';
				} // else
				$hidden[$posted_items['newskin_hidden_name_' . $i]] = $h_val;
			} // if isset post[hidden name]
			
		} // for i in range 1 to 5, inclusive
		if (!empty($hidden))
		{
			$optin_options['hidden_fields'] = $hidden;
		} // if
		
		if (!empty($optin_options)) // might be null if "none" option is selected.
		{
			$optin_options['service'] = $optin_choice;
			$skin_data['optin_settings'] = $optin_options;
		} // if
		
		
		/* EXCLUDE CATEGORIES */
		if (isset($posted_items['newskin_exclude_cats']) 
			&& $posted_items['newskin_exclude_cats'] != 'Select from all Categories')
		{
			$skin_data['exclude_categories'] = $posted_items['newskin_exclude_cats'];
		} // if 
		
		/*  DESIGN OPTIONS */
		// Initialize an associative array that will contain all final design option names and values.
		$design_options = array();
		$required_fonts = array(); // Contains names of needed Google Fonts
		/* 
			Initialize an array that will contain all of the fonts that need to be added.
			Whenever a Google font type is found in the appearance options, 
			the required font is appended to this list. 
			Therefore, when we create the static HTML file, we just add the required fonts. 
		*/
		$fonts_to_add = array();
		// There is a limit sometimes on POST variable length (around 64 characters)
		// Therefore, I keep the name variable short, by using "dsg-set_" instead of "design..."
		$app_prepended = 'dsg-set_'; // the prepend that signifies an appearnce setting.
		$prepend_len = strlen($app_prepended); // count the length of the above.
		// initialize an associative array that contains all user-added style properties for this skin.
		$style = array();
		// Get the template and form template, which would have been submitted in a hidden field.
    
    
		if (!empty($posted_items['template-url']) && 
		  trim($posted_items['template-url']) != '') {
		  $template = ois_get_external_file(urldecode($posted_items['template-url']));
		}
		
		if (!empty($posted_items['template-form-url']) && 
		  trim($posted_items['template-form-url']) != '') {
		  $template_form = ois_get_external_file(urldecode($posted_items['template-form-url']));
		}
		
		$template_css = urldecode($posted_items['template-css-url']); // this only has the path, not the content.
		
		foreach ($posted_items as $name=>$val) {
			if (substr($name, 0, $prepend_len) == $app_prepended) {
				// ^ If the name is prepended with the appearance prepend.
				// Save these settings for now.
				$design_options[substr($name, $prepend_len )] = $val;
				
				// Extract the useful information from the name, minus the prepend.
				$property_name = substr($name, $prepend_len, strlen($name));
				$attr_items = explode("-", $property_name);
				
				if ($attr_items[0] == 'text' || $attr_items[0] == 'placeholder' || $attr_items[0] == 'textarea' || ($attr_items[0].'-'.$attr_items[1]) == 'button-text' || $attr_items[0] == 'align' || $attr_items[0] == 'img')
				{ 
					/* 
						The attr is type of text, i.e. not style, for example.
						In this case, when we build the static HTML file, we can just replace these
						property names (e.g. "title-text") with their values (e.g. "Hello World").
						
						We need to separate even these items to get more information. Information is
						densely packed into the variable name.
						
						I understand that the next section is a little confusing. Stay with me.
						Example are helpful (Examples follow below):
							$name = design-setting_text-ois-1-title_text
							$property_name = text-ois-1-title_text,
								or placeholder-text-ois-1-email-input_placeholder-text,
								or style-ois-1-outer_width
							$attr_items = array(text, ois, 1, title_text),
								or array(placeholder, text, ois, 1, email, input_placeholder, text) 
					*/
					
					// find the id for this feature
					$second_last = count($attr_items) - 1;
					if ($attr_items[0] == 'placeholder') 
					{
						$id = implode('-', array_slice( $attr_items , 4, $second_last - 4));
						// Results in, e.g.: email-input_placeholder
						$id = str_replace('_', '-', $id);
						
						// E.g. $id = email-input-placeholder
						// Now, replace the variable name in the template_form with this value.

						$template_form = str_replace("{{" . $id . "}}", $val, $template_form);
					} // if placeholder
					else if (($attr_items[0].'-'.$attr_items[1]) == 'button-text')
					{
						$id = implode('-', array_slice( $attr_items , 2, $second_last));
						// Results in, e.g.: email-input_placeholder
						$id = str_replace('_button-', '-', $id);
						// E.g. $id = email-input-placeholder
						// Now, replace the variable name in the template_form with this value.
						$template_form = str_replace("{{" . $id . "}}", $val, $template_form);
					} // else if button-text
					else if ($attr_items[0] == 'align')
					{
						// Exactly the same as text.
						$id = implode('-', array_slice( $attr_items , 1, $second_last));
						$id = str_replace('_' . $attr_items[0], '', $id);
						
						$css_attr = "text-align: $val !important;";
						$sa = explode('_', $property_name); // style attribute
						$element = substr($sa[0], 6);
						
						if (!empty($style[$element])) {
							array_push($style[$element], $css_attr);
						} // if 
						else 
						{
							$style[$element] = array($css_attr);
						} // else
					}
					else
					{
						$id = implode('-', array_slice( $attr_items , 1, $second_last));
						$id = str_replace('_' . $attr_items[0], '', $id);
						// E.g. $id = ois-1-title
						
						// Replace the template.
						$template = str_replace("{{" . $id . "}}", $val, $template);
						// And form template, too
						$template_form = str_replace("{{" . $id . "}}", $val, $template_form);
					} // else
					
				} // if text
				else if ($attr_items[0] == 'style')
				{	
					$sa = explode('_', $property_name); // style attribute
					// ^ e.g.: style-ois-1-outer_width
					$style_attr = $sa[1];
					// ^ e.g.: width
					
					$element = substr($sa[0], 6);
					// ^ e.g.: ois-1-outer
					$css_attr = "$style_attr: $val !important;";
					// e.g.: width: 200px !important;
					
					// Add this style property to an array of properties for this element.
					if (!empty($style[$element])) {
						array_push($style[$element], $css_attr);
					} // if 
					else 
					{
						$style[$element] = array($css_attr);
					} // else
				} // else if style
				else if ($attr_items[0] == 'font')
				{
					$sa = explode('_', $property_name); // style attribute
					$element = substr($sa[0], 5);
					// ^ e.g.: ois-1-title
					
					$google_font_pre = 'googlefont-';
					if (substr($val, 0, 11) == $google_font_pre)
					{
						$val = substr($val, 11, strlen($val));
						array_push($required_fonts, $val); 
					} // if Google Web Font
					
					$css_attr = "font-family: '$val', Helvetica, sans-serif !important;";
					// e.g.: font-family: 'Open Sans', Helvetica, sans-serif !important;
					
					// Add this style property to an array of properties for this element.
					if (!empty($style[$element])) {
						array_push($style[$element], $css_attr);
					} // if 
					else 
					{
						$style[$element] = array($css_attr);
					} // else
					
				} // else if font
				
			} // if a design element
		} // for each post_item
		
		
		//exit;
		
		$skin_data['appearance'] = $design_options;
		/* 
			FADE IN EFFECT 
			The number of seconds, if it's fading, after which it would begin to appear on the page.
		*/
		if (empty($posted_items['fade_sec']))
		{ // it must be an integer, otherwise just 0.
			$skin_data['fade_sec'] = '0';
		} // if not fade_sec
		else
		{
			$skin_data['fade_sec'] = $posted_items['fade_sec'];
		} // else
		
		/*
			AFFILIATE INFORMATION
			Whenever the user makes a new skin, we don't want them to reenter their
			affiliate username. Therefore, we ought to save this information in a new wp option.
		*/
		if (isset($posted_items['aff_enable']) 
			&& isset($posted_items['aff_user']))
		{
			 // Is affiliate links enabled? What is the user's affiliate username?
			$skin_data['aff_enable'] = $posted_items['aff_enable'];
			// The user can use different affiliate usernames for different skins, though.
			$aff_username = $posted_items['aff_user'];
			$skin_data['aff_username'] = $aff_username;
			update_option('ois_aff_user', $aff_username);
			
		} // if isset
		
		/* SKIN FONTS (GOOGLE FONTS) */
		$skin_data['google_fonts'] = $fonts_to_add; // which fonts does this skin require?
		
		/* SAVE DATA TO WP OPTIONS DATABASE */
		//update_option('ois_skins', array());
		$existing_skins = get_option('ois_skins');
		$skin_id = $posted_items['skin-id'];
		$skin_data['id'] = $skin_id;
		// Potentially overwrite the skin as it exists in the database.
		$existing_skins[$skin_id] = $skin_data;
		
		/* UPDATE EXISTING SKINS */
		update_option('ois_skins', $existing_skins);

		/* CREATE A STATIC FILES FOR THIS TEMPLATE AND STYLE */
		// Wrap the template appropriately.
		
		
		// Affiliate Options
		if (isset($posted_items['aff_enable']) 
			&& isset($posted_items['aff_user']))
		{
			// The user can use different affiliate usernames for different skins, though.
			$aff_username = $posted_items['aff_user'];
			// This is messy, I know.
			$template .= '<div style="padding-top:7px;text-align:center;"><a href="http://'  . $aff_username . '.optinskin.hop.clickbank.net" style="border:none;"><img style="border:none;" src="' . WP_PLUGIN_URL . '/OptinSkin/front/images/poweredby.png" /></a></div>';
			
		} // if isset
		
		// Mobile wrapper
		if (isset($posted_items['disable_mobile']))
		{
			$mobile_option = $posted_items['disable_mobile'];
			// Options are: show_large, show_small, show_all
			if ($mobile_option == 'show_small')
			{
				$template = "<div class='visible-sm visible-xs'>$template</div>";
			} // else if
			else if ($mobile_option == 'show_large')
			{
				$template = "<div class=' visible-md visible-lg'>$template</div>";
			} // else if
		} // if
		
		// ois-design is crucial, and needs to wrap even the mobile wrapper.
		$classes = 'ois-design';
		if (!empty($posted_items['special_fade']) && $posted_items['special_fade'] == 'yes') 
		{
			$classes .= ' ois-fade';
			$data = 'data-ois-fade-sec="' . $posted_items['fade_sec'] . '"';
		}
		else
		{
			$data = '';
		}
		
		$template = "<div id='ois-$skin_id' class='$classes' $data>$template</div>";
		
		/* OPTIN FORM */
		// We need to take the template_form and add the appropriate values.
		if ($optin_choice != 'none')
		{
			$template = str_replace('{{optin_form}}', $template_form, $template);
			$template = ois_render_form($skin_id, $optin_options, $template);
		} // if
		else
		{
			$template = str_replace('{{optin_form}}', '', $template);
		} // else
		
		
		// format properties for CSS.
		$css_final = ois_form_css($skin_id, $style, $template_css, $required_fonts, $margins); 
		ois_create_skin_files($skin_id, $template, $css_final);
    
		/* REDIRECT USER TO APPROPRIATE NEXT STEP */
		$updated_message = '&updated=true';
		$cur_location = explode("?", $_SERVER['REQUEST_URI']);
		$new_location =
			'http://' . $_SERVER["HTTP_HOST"] . $cur_location[0] . '?page=ois-' . $skin_id;
		echo '<script type="text/javascript">
			window.location = "' . $new_location  . '";
		</script>';
	} // if isset $posted_items
} // ois_handle_new_skin()


/*
	Desc: Forms the CSS content that will later be entered into a .css file.
*/
function ois_form_css($skin_id, $style, $template_css, $required_fonts, $margins)
{
	$date = date("F j, Y, g:i a");
	$css_final = "/*\n\tStylesheet for OptinSkin; ID: $skin_id.\n\tGenerated: $date\n*/\n";
	if ($template_css != '')
	{
		$css_final .= "/* From original stylesheet\n \t at $template_css */\n" . 
			ois_get_external_file($template_css) . "\n" .
			"/* End original stylesheet */\n";
	} // if
	if (!empty($required_fonts))
	{
		$css_final .= "/* Google Font stylesheets */\n";
		$google_base_url = 'http://fonts.googleapis.com/css?family=';
		$font_id;
		foreach ($required_fonts as $g_font)
		{
			$font_id = str_replace(' ', '+', $g_font);
			$font_url = $google_base_url . $font_id;
			$css_final .= ois_get_external_file($font_url);
		} // foreach
		
		$css_final .= "/* End Google Fonts */\n";
	} // if
	
	$css_final .= "/* Generated from custom design options */\n";
	foreach ($style as $element_id=>$css)
	{
		$css_final .= "#ois-$skin_id .$element_id {\n"; // always a class instead of id
		foreach ($css as $cssPV)
		{
			$css_final .= "\t$cssPV\n";
		} // foreach
		
		// This would be a good place to add the margins.
		
		if (substr($element_id, -5) == "outer") {
  		// Add the styles for the margin...
  		$margin_style = "";
      if (!empty($margins)) {
        foreach ($margins as $margin_type=>$margin_set) {
          $margin_style .= "\tmargin-$margin_type: " . $margin_set . ";\n";
        } // foreach
        $css_final .= $margin_style;
      } // if
		}
		
		$css_final .= "}\n";
	} // foreach
	$css_final .= "/* End custom style */\n/* End of file */";
	
	return $css_final;
} // ois_form_css

function ois_render_form($skin_id, $options, $template)
{
	include_once('admin_form_functions.php');
	
	if (empty($options))
	{
		return $template; // no form at all!
	} // if
	
	$service = $options['service'];
	switch($service)
	{
		case 'feedburner':
			return ois_render_feedburner($skin_id, $options, $template);
			break;
		case 'aweber':
			return ois_render_aweber($skin_id, $options, $template);
			break;
		case 'mailchimp':
			return ois_render_mailchimp($skin_id, $options, $template);
			break;
		case 'getResponse':
			return ois_render_getresponse($skin_id, $options, $template);
			break;
		case 'infusionSoft':
			return ois_render_infusionsoft($skin_id, $options, $template);
			break;
		case 'custom':
			return ois_render_custom($skin_id, $options, $template);
			break;
		case 'other':
			return ois_render_other($skin_id, $options, $template);
			break;
	} // switch
} // ois_render_form ()

function ois_create_skin_files($skin_id, $template, $css_final)
{
	
	$skin_path = OIS_PATH . "skins/$skin_id";
	if ( !file_exists( $skin_path ) ) {
		mkdir( $skin_path, 0777, true );
	} // if
	
	file_put_contents("$skin_path/static.html", stripslashes($template));
	file_put_contents("$skin_path/style.css", stripslashes($css_final));
		
} // ois_create_skin_files($skin_id, $template, $css_final)
?>