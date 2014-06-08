<?php
include_once('admin_add_functions.php');

function ois_has_saved()
{
	if (isset($_POST['design']))
	{
		// The user has posted data. Save these settings.
		include_once('admin_save_skin.php');
		ois_handle_new_skin();
	} // if
} // ois_has_saved()


/**
 * add_extensions function.
 *
 * If there are saved extensions, print javascript code
 * that will enable loading those extended designs.
 * 
 * @post prints a javascript string (as it were) array
 *  e.g. ['http://www.optinskin.com/src/']
 *
 * @access public
 * @return void
 */
function ois_add_extensions ()
{
  $extensions = get_option('ois_extensions');
  if (!empty($extensions)) {
    
    // Make a JavaScript array of the extensions
    $js_ext_arr = '[';
    foreach($extensions as $name=>$url) {
      // The URL is the only relevant datum
      $js_ext_arr .= "'$url',";
    } // foreach
    
    // Remove the last comma
    $js_ext_arr = substr($js_ext_arr, 0, strlen($js_ext_arr) - 1) . ']';
    echo $js_ext_arr;
  } else {
    // empty array
    echo 'new Array()';
  } // else
}

function ois_add_new() {


	//update_option('ois-valid', 'no');

	$ois_valid = get_option('ois-valid');
	if ($ois_valid == 'yes')
	{
		// Get license key
		$key = get_option('ois-key');
		$home_url = get_option('ois-home-url');
	} // if
	else
	{
		// Need to redirect here.
		$uri = explode('?', $_SERVER['REQUEST_URI']);
		$validation_url = $uri[0] . '?page=ois-license-key';
		?>
		<script type="text/javascript">
			window.location.href = '<?php echo $validation_url ?>';
		</script>
		<?php
	}

	//$key = '12345';
	/*
		CHECK IF SAVED
		If the user has saved at this point, save data and move to new page.
	*/
	ois_has_saved();

	$design_choice = 1; // The design that is loaded for this skin.
	$all_skins = get_option('ois_skins'); // load all of the created skins
	$editing = false;
	/*
		CHECK IF EDITING A SKIN
	*/
	if (isset($_GET['id']))
		{ // If we are editing a skin.
		$skin_id = $_GET['id'];
		if (!empty($all_skins))
		{
			$this_skin = $all_skins[$skin_id];
			$editing = true;
		} // if
	} // if

	/*
		CHECK IF DUPLICATING A SKIN
	*/
	else if (isset($_GET['duplicate']))
		{
			// Duplicating is still creating, so we need a new skin ID.
			$skin_id = ois_generate_new_id($all_skins);

			$dup_id = $_GET['duplicate'];
			if (!empty($all_skins))
			{
				// Take all the properties of this skin
				$this_skin = $all_skins[$dup_id];
			} // if
		} // else if

	/*
		CREATING NEW SKIN
	*/
	else
	{
		$skin_id = ois_generate_new_id($all_skins);
		$this_skin = array(); // just an empty array new a new skin.
	} // else

	if ($editing)
	{
		// If we are editing a skin
		ois_editing_heading($skin_id, $this_skin['title'], $this_skin['status']);

	} // if
	else {
		ois_section_title('Create a New Skin', 'Here you can design an OptinSkin to place anywhere in your Wordpress website.', '');

	} // else

	if (isset($_GET['update']))
	{
		if ($_GET['update'] == 'delete')
		{
			ois_notification('Your Skin has Been Successfully Deleted', '', '');
		} // if
		// There could be other types here.
	} // if

	/*
		SKIN TITLE AND DESCRIPTION
		Load data and create "initialization" interface.
	*/
	if (!empty($this_skin))
	{
		$skin_title = stripslashes($this_skin['title']);
		//$skin_desc = stripslashes($this_skin['description']);
		$design_choice = $this_skin['design'];
	} // if
	else
	{
		// Creating a new skin.
		$skin_title = '';
		$skin_desc = '';
	} // else

	// CUSTOM DESIGNS
	$custom_designs = get_option('ois_custom_designs');
	$custom_design_content = array();

	if (!empty($custom_designs))
	{
		foreach ($custom_designs as $custom_design_id)
		{
			// $custom_design_id here in an integer
			$custom_path = OIS_PATH . "customDesigns/$custom_design_id";
			$css_url = OIS_URL . "customDesigns/$custom_design_id/style.css";

			if (file_exists($custom_path))
			{
				$cust_html = file_get_contents("$custom_path/static.html");
				array_push($custom_design_content, array(
						'html' => $cust_html,
						'css' => $css_url // just the path is required
					)
				);
			} // if
		} // foreach
	} // if

	/*
		Set hidden input to skin ID
	*/
?>
	<script type="text/javascript">
		var skinID = <?php echo $skin_id ?>;
		var curDesign = <?php echo $design_choice ?>;
		var extUrl = "<?php echo OIS_EXT_URL ?>";
		var customDesigns = <?php echo json_encode($custom_design_content); ?>;
		var licenseKey = "<?php echo $key; ?>";
		var homeUrl = "<?php echo $home_url; ?>";
		// Add extensions
		var extensions = <?php ois_add_extensions() ?>;
		console.log("Extensions: " + extensions);

/* 		console.log(customDesigns); */

		var savedSettings = {};
		<?php
	// Settings
	if (!empty($this_skin['appearance']))
	{
		echo 'savedSettings = { ';
		foreach ($this_skin['appearance'] as $key => $val)
		{
			echo "'$key': '" . str_replace("\r", "", str_replace("\n", "", $val)) . "', ";
		}
		echo ' };';
	}
?>
	</script>
	<?php

	ois_add_init_table($skin_title, '');

?>
	<div class="alert alert-warning" style="padding: 10px; border: 1px solid #8e44ad; background-color: #9b59b6; color: #fff; font-weight: 100;">Please note that some of the social sharing buttons will not work within this admin area. This is because you could not share this password-protected page, on Facebook, Twitter, etc. The buttons should function properly on your posts, though.</div>
	<?php
	ois_start_table('Customize Design', 'mantra/Colours.png');
	$data = array (
		'title' => 'Skin Design',
		'description' => 'Select one of our pre-made (and tested) designs using the controllers.',
		'style' => 'text-align:center !important; padding: 10px !important;',
		'alternative' => 'yes',
		'inner_style' => 'width:120px'
	);
	ois_option_label($data);
	// Load the designs carousel.
?>
	<div id="ois-control-area">
		<!-- Buttons to control the current design -->
		<div id="ois-design-num-display">
			<span id="ois-current-design">0</span>/<span id="ois-num-designs">0</span>
		</div> <!-- #ois-design-num-display -->

		 	    
	     <div style="clear:both"></div>
		<div id="ois-design-area-wrapper">
			<a href="#" id="previous-design" class="ois-change-design-button"></a>

 <a href="#" id="next-design" class="ois-change-design-button"></a>


			<div id="ois-design-area" class="ois-design"></div> <!- /design-area -->
				<div style="clear:both;"></div><!-- clear both -->
		</div><!-- design-area-wrapper -->
	</div> <!- /control-area -->

	<?php
	// I don't think we need this carosel anymore
	//ois_create_carousel($design_to_use, $skin_to_use);
	ois_option_end();

	/* This is where the preview is going go */
?>
	<!-- we need iris -->
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script><!-- for slider bars, and Iris by Automattic -->
	<script src="<?php echo OIS_URL ?>admin/addSkin/js/iris.min.js" type="text/javascript"></script><!-- the color-picker -->

	<script src="<?php echo OIS_EXT_URL ?>min/script3.4.min.js" type="text/javascript"></script><!-- Design controls, etc. -->

	<script type="text/javascript" src="<?php echo OIS_URL ?>admin/addSkin/js/add_skin.js"></script> <!-- Validation; changes according to selected service provider; etc. -->

<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css"><!-- for slider bars -->
	<link href="<?php echo OIS_EXT_URL ?>min/normalize.min.css" rel="stylesheet" />
	<link href="<?php echo OIS_URL ?>admin/addSkin/css/style.css" rel="stylesheet" />
	<link href="<?php echo OIS_URL ?>admin/css/glyphicons.bootstrap.min.css" rel="stylesheet" />

	<!-- some necessary form elements -->
	<?php $saved = "false";  // #todo ?>
	<input type="hidden" id="hidden-design" name="design" value="1" />
	<input type="hidden" id="hidden-skin-id" name="skin-id" value="<?php echo $saved; ?>" />
	<input type="hidden" id="hidden-template-url" name="template-url" value='' />
	<input type="hidden" id="hidden-template-css-url" name="template-css-url" value='' />
	<input type="hidden" id="hidden-template-form-url" name="template-form-url" value="" />


	<?php
	$data = array(
		'title' => 'Design Options for This Skin',
		'description' => 'Certain skins allow you to customize aspects of its design.',
		'inner_style' => 'width:110px;',
	);
	ois_option_label($data);
?>
	<div id="ois-editing-area"><h3>Loading editing controls...</h3></div> <!- /editing-area -->
	<div style="clear:both" id="ois-non-control"></div>
	<?php

	ois_end_option_and_table();


	/*
		OPTIN SERVICE SETTINGS
	*/
	/*
		OPTIN ACCOUNT DEFAULTS
		We have stored optin information history, for user convenience.
	*/
	$optin_accounts = array (
		'feedburner-id' => get_option('ois_feedburner_id'),
		'mailchimp-form' => get_option('ois_mailchimp_form'),
		'aweber-id' => get_option('ois_aweber_id'),
		'icontact-html' => get_option('ois_icontact_html'),
		'other-html' => get_option('ois_other_html'),
		'getResponse-id' => get_option('ois_getResponse_id'),
		'getResponse-html' => get_option('ois_getResponse_html'),
		'infusionSoft-html' => get_option('ois_infusionSoft_html'),
	);
	ois_start_table('Optin Form Settings', 'mantra/Mail.png');
	ois_option_label(array('title' => 'Optin Service for this Skin', 'description' => '' ));

	// Create an array of choices for optin services
	$optin_services = array (
		'feedburner' => array (
			'ID' => 'feedburner-id',
		),
		'aweber' => array (
			'List Name (e.g. \'viperchill\')' => 'aweber-id',
		),
		'mailchimp' => array (
			'Naked Form HTML' => 'mailchimp-form',
		),
		/*
		'icontact' => array (
			'Form HTML' => 'icontact-html',
		),
*/
		'getResponse' => array (
			'Webform ID' => 'getResponse-id',
			'Form HTML' => 'getResponse-html',
		),
		'infusionSoft' => array (
			'Form HTML' => 'infusionSoft-html',
		),
		'custom' => array (
			'Form Action<br/><small>E.g. http://www.aweber.com/scripts/addlead.pl</small>' => 'custom-action',
			'Email name-value<br/><small>E.g. EMAIL</small>' => 'custom-email',
			'Name name-value (Optional)<br/><small>E.g. FNAME</small>' => 'custom-name',
		),
		'other' => array (
			'Form HTML' => 'other-html',
		),
	); // optin services

	// Display these services as input choices
	if (!empty($this_skin) && isset($this_skin['optin-service']))
	{
		$optin_choice = $this_skin['optin-service'];
	}
	else
	{
		$optin_choice = 'feedburner';
	}
  
  echo "<p style='padding: 15px 0 5px 5px;'><span class='glyphicon glyphicon-info-sign' style='margin-right:5px'></span> You need to have a third-party service to collect your email addresses.<br/>For example, MailChimp (www.mailchimp.com) is a free, fully-featured service that will do this. Aweber (www.aweber.com) is also a popular choice.</p>";
  
  echo "<p>Select which service you are using:</p>";
  echo '<div id="ois-optin-choices" style="margin-bottom:10px;margin-top:5px;">';
  $service_count = 0;
	foreach ($optin_services as $name=>$data) { 
  	$service_count++;
  	if ($service_count == 5) {
    	echo "<br/>";
  	} // if
	?>
		<span class="ois-optin-choice-holder" style="margin-bottom:2px;">
			<input class="ois_optin_choice" type="radio" name="newskin_optin_choice"
						<?php
		if ($optin_choice == $name)
		{
			echo 'checked="checked"';
		} // if
		?> value="<?php echo $name; ?>" />
			<img style="padding:0 2px;margin-top:-3px;width:18px!important;" src="<?php echo OIS_URL . 'admin/images/' . strtolower($name) . '.png'; ?>" /><?php
		echo ucwords($name);
		echo '</span> ';
	} // foreach optin service 
	
	?>
		<span style="padding: 2px 12px 2px 5px;">
			<input
				class="ois_optin_choice"
				type="radio"
				name="newskin_optin_choice"
				value="none"
			<?php
	if ($optin_choice == 'none')
	{
		echo 'checked="checked"';
	} // if ?>
		/> None </span>
		
		</div> <!-- ois-optin-choices -->
	<?php
    
  echo "<p style='margin-top:15px;margin-bottom:-5px'>Please provide some details so that we can communicate with this service:</p>";
	ois_option_end();
	
	
	foreach ($optin_services as $name=>$data) {
	  
		if ($name != 'icontact') {
			$ser_title = ucwords($name);
		} else {
			$ser_title = 'iContact';
		}
		if ((empty($this_skin['optin_settings']) && $name != 'feedburner') || (!empty($this_skin['optin_settings']) && trim($this_skin['optin_settings']['service']) != $name)) {
			$inner_st = 'display:none';
		} else {
			$inner_st = '';
		}
		ois_option_label(array( 'title' => 'Optin Info for ' . $ser_title, 'description'=>'', 'class' => 'ois_optin_account ois_optin_' . $name,  'style' => $inner_st));
    
    
    // Create an "options" array for easy passing to functions
    $options = array();
    if (!empty($this_skin['optin_settings'])) {
      $options = $this_skin['optin_settings'];
    } // if
    
    switch ($name) {
      case 'mailchimp':
        mailchimp_options($options, $optin_accounts);
        break;
      case 'aweber':
        aweber_options($options, $optin_accounts);
        break;
      case 'feedburner':
        feedburner_options($options, $optin_accounts);
        break;
      case 'infusionSoft':
        infusionsoft_options($options, $optin_accounts);
        break;
      case 'getResponse':
        getresponse_options($options, $optin_accounts);
        break;
      case 'custom':
        custom_options($options, $optin_accounts);
        break;
      case 'other':
        other_options($options, $optin_accounts);
        break;
    } // switch (String)
    
    ois_end_option_and_table();
	}
	
	ois_option_end();
	ois_option_label(array('title' => 'Extra Hidden Fields (Optional)', 'description' => 'Optional hidden values for campaign tracking, etc.'));
	
	echo "<p>If you require any additional \"hidden\" fields, add the name and values of each field:</p>";
    
    $i = 1;
    if (!empty($this_skin['optin_settings']) && !empty($this_skin['optin_settings']['hidden_fields'])) {
      foreach ($this_skin['optin_settings']['hidden_fields'] as $hidden_name=>$hidden_value)
      {
  
  		ois_inner_label(array('title' => 'Hidden Field ' . $i,
  				'description' => 'Optional'));
  ?>
  		<label for="ois_hidden_name_<?php echo $i; ?>">Name </label><input type="text" class="ois_textbox" id="ois_hidden_name_<?php echo $i; ?>" name="newskin_hidden_name_<?php echo $i; ?>" value="<?php echo $hidden_name ?>" />
  		<label for="ois_hidden_value_<?php echo $i; ?>">Value </label><input type="text" class="ois_textbox" id="ois_hidden_value_<?php echo $i; ?>" name="newskin_hidden_value_<?php echo $i; ?>" value="<?php echo $hidden_value ?>" />
  		<?php
  		ois_option_end();
          $i++;
  	} // foreach
	}
    
    $hidden_name = '';
    $hidden_value = '';
    while ($i <= 5)
    {
        ois_inner_label(array('title' => 'Hidden Field ' . $i,
				'description' => 'Optional'));
?>
		<label for="ois_hidden_name_<?php echo $i; ?>">Name </label><input type="text" class="ois_textbox" id="ois_hidden_name_<?php echo $i; ?>" name="newskin_hidden_name_<?php echo $i; ?>" value="<?php echo $hidden_name ?>" />
		<label for="ois_hidden_value_<?php echo $i; ?>">Value </label><input type="text" class="ois_textbox" id="ois_hidden_value_<?php echo $i; ?>" name="newskin_hidden_value_<?php echo $i; ?>" value="<?php echo $hidden_value ?>" />
		<?php
            ois_option_end();
        $i++;
    } // while
    
	ois_end_option_and_table();
	ois_option_end();
	
	ois_end_option_and_table();

	/*
		SKIN PLACEMENT OPTIONS
	*/
	ois_start_table('Placement Options', 'mantra/Designs.png');
	ois_option_label(array('title' => 'Automatic Skin Placement',
			'description' => ''));

	if (!empty($this_skin['below_x_paragraphs']))
	{
		$below_x_paragraphs = $this_skin['below_x_paragraphs'];
	} // if
	else
	{
		$below_x_paragraphs = 2;
	} // else
	if (!empty($this_skin['scrolled_past']))
	{
		$scrolled_past = $this_skin['scrolled_past'];
	} // if
	else
	{
		$scrolled_past = '100px';
	} // else

	$positions = array (
		'post_bottom' => 'At the bottom of posts',
		'post_top' => 'At the top of posts',
		'below_first' => 'Below the first paragraph',
		'floated_second' => 'Floated right of second paragraph',
		'sidebar' => 'In a custom location, such as the sidebar using a widget, or post using a shortcode',
		'below_x_paragraphs' => 'Below <input type="text" style="width:35px; height: 22px; margin:0;padding:0 0 0 5px;" class="ois_textbox" value="' . $below_x_paragraphs . '" name="below_x_paragraphs" /> paragraphs',
	);

	if (isset($this_skin['post_position']))
	{
		$cur_position = $this_skin['post_position'];
	} // if
	else
	{
		$cur_position = 'post_bottom'; // By default.
	} // else

	$i = 0;
	echo "<p style='padding: 10px 0 0 5px;'>Select a position for your skin:</p>";
	echo '<table>';
	foreach ($positions as $position=>$description) {
		if ($i % 2 == 0)
		{
			echo '<tr>';
		} // if

		echo '<td style="width: 260px;">';
		echo '<input type="radio" class="new_skin_post_type"
			name="post_position" value="' . $position . '"';

		if (trim($cur_position) == '')
		{
			if ($i == 0)
			{
				echo 'checked="checked"';
			} // if
		} // if
		else
		{
			if ($cur_position == $position) {
				echo 'checked="checked"';
			} // if
		} // else
		echo ' /> ';
		echo $description;
		echo '</td>';
		if ($i % 2 != 0)
		{
			echo '</tr>';
		} // if
		$i++;
	} // foreach position

	echo '</tr>';
	ois_table_end(); // ends the positions table
	echo '<p style="color: #666; padding-left: 5px; padding-top: 5px;">
				<span class="glyphicon glyphicon-info-sign" style="margin-right:5px"></span> Once the skin is created, a widget with the skin will be available for sidebar use.<br/>You will also receive a shortcode to insert the skin anywhere else.
			</p>';
	ois_option_end();
	ois_option_label(array('title' => 'Post Exceptions',
			'description' => ''));
	echo '<p>Is there anywhere that you don\'t want this skin to appear? Specify below:</p>';
	ois_inner_label(array('title' => 'Post IDs<br/><small>e.g. <em>15,27,32</em>.</small>'));
	
	echo '<input type="text" style="width:200px;" id="ois_exclude_posts" class="ois_textbox" name="exclude_posts"';
	if (!empty($this_skin['exclude_posts']))
	{
		echo ' value="' . $this_skin['exclude_posts'] . '"';
	} // if
	echo ' />';
	
	echo '<select id="ois_select_post">';
	echo '<option>Select from all posts</option>';
	$all_posts = get_posts();

	foreach ( $all_posts as $post )
	{
		$option = '<option value="' . $post->ID . '">';
		$post_title = $post->post_title;
		if (strlen($post_title) > 23) {
  		$post_title = substr($post_title, 0, 20) . "...";
		} // if
		$option .= $post_title;
		$option .= '</option>';
		echo $option;
	} // foreach categoriy
?>
			</select>
			<a href="#" id="ois_excl_post" class="ois_secondary_button" >Add To List</a>
	<?php
	
	
	//<small style="margin-left:15px;"><a href="http://optinskin.com/faq/" target="_blank">Need to know how to find the post ID?</a></small>';
	ois_table_end();

	ois_inner_label(array('title' => 'Category IDs<br/><small>e.g. <em>1,3,4</em></small>'));
	echo '<input type="text" class="ois_textbox" id="ois_exclude_cats" name="newskin_exclude_cats" style="width:240px;"';
	if (!empty($this_skin['exclude_categories']))
	{
		echo ' value="' . $this_skin['exclude_categories'] . '"';
	} // if
	echo ' />';
	echo '<select id="ois_select_cat">';
	echo '<option>Select from all categories</option>';
	$cats = get_categories();

	foreach ( $cats as $cat )
	{
		$option = '<option value="' . $cat->cat_ID . '">';
		$option .= $cat->cat_name;
		$option .= '</option>';
		echo $option;
	} // foreach categoriy
?>
			</select>
			<a href="#" id="ois_excl_cat" class="ois_secondary_button" >Add To List</a>
	<?php
	ois_option_end();
	ois_table_end();
	ois_option_end();
	ois_option_label(array('title' => 'Spaces Around the Skin',
			'description' => '',
			'image' => 'spacing.png'));
	$margins = array();
	if (!empty($this_skin['margins'])) {
		$margins = $this_skin['margins'];
	} else {
		$margins = array( // default margins
			'top' => '5px',
			'right' => '0px',
			'bottom' => '5px',
			'left' => '0px',
		);
	}
	ois_inner_label(array('title' => 'Space Above and Below'));
	echo '<div style="margin-left:5px;">
			<p>Extra Space Above Skin:
				<input type="text" class="ois_textbox" value="' . $margins['top'] . '" style="width:70px; margin-left:15px;" name="margin_top" />
			</p>';
	echo '<p>Extra Space Below Skin:
				<input type="text" class="ois_textbox" value="' . $margins['bottom'] . '" style="width:70px; margin-left:15px;" name="margin_bottom" /></p></div>';
	ois_table_end();
	ois_inner_label(array('title' => 'Space Left and Right'));
	
	echo '<div class="checkbox" style="margin:15px;">
    <label>
      <input type="checkbox" id="ois-center-skin" name="skin_center"';
      if (!empty($this_skin['skin_center']) && $this_skin['skin_center'] == 'center') {
        echo ' checked="checked"';
      } // if skin_center is checked
      echo ' value="center"> <span style="vertical-align:bottom">Center skin horizontally</span>
    </label>
  </div>';
	echo '<div style="margin-left:5px;"><p>Extra Space to Left of Skin:
			<input type="text" class="ois_textbox" value="' . $margins['left'] . '" style="width:70px; margin-left:15px;" name="margin_left" id="ois-margin-left" /></p>';
	echo '<p>Extra Space to Right of Skin:
			<input type="text" class="ois_textbox" value="' . $margins['right'] . '" style="width:70px; margin-left:15px;" name="margin_right" id="ois-margin-right" /></p></div>';
	ois_table_end();
	ois_inner_label(array('title' => 'Margin Type'));
	if (!empty($this_skin['margin_type']))
	{
		$margin_type = $this_skin['margin_type'];
	} // if
	else
	{
		$margin_type = 'margin';
	} // else
	echo '<p>
		<span><input type="radio" name="margin_type"';
	if (trim($margin_type) == 'margin') {
		echo ' checked="checked"';
	}
	echo ' value="margin" /> Margin</span>

		<span style="margin-left: 15px;"><input type="radio" name="margin_type"';
	if (trim($margin_type) == 'padding') {
		echo ' checked="checked"';
	}
	echo ' value="padding" /> Padding</span>
		</p>';
	ois_table_end();

	ois_option_label(array('title' => 'Special Effects', 'description' => 'Get more attention to your Optin-Form', 'image' => 'fade.png'));
	ois_inner_label(array('title' => 'Fade In'));

	echo '<p><input type="checkbox" name="special_fade"';
	if (isset($this_skin['special_fade']) && $this_skin['special_fade'] == 'yes')
	{
		echo ' checked="checked"';
	} // if
	if (isset($this_skin['fade_sec']) && trim($this_skin['fade_sec']) != '')
	{
		$fade_sec = $this_skin['fade_sec'];
	} // if
	else
	{
		$fade_sec = '3'; // default
	} // else


	echo ' value="yes" /> Enable <span style="margin-left: 10px;">Fade in after <input type="text" class="ois_textbox" name="fade_sec" style="width: 45px;" value="' . $fade_sec . '" /> seconds.</span></p>';
	echo '<p style="color: #666;">Fades into existence once the skin is visible to the user, drawing attention.</p>';
	ois_end_option_and_table();

	/*
	ois_inner_label(array('title' => 'Stick to Top'));
	echo '<p><input type="checkbox" name="special_stick"';
	if (isset($this_skin['special_stick']) && $this_skin['special_stick'] == 'yes')
	{
		echo ' checked="checked"';
	} // if
	echo ' value="yes" /> Enable </p>';
	echo '<p style="color: #666;">Stays at the top of the screen once your user scrolls past.</p>';

	ois_end_option_and_table();
*/

	ois_option_label(array('title' => 'Responsiveness',
			'description' =>
			'In the case of mobile phones, tablets, etc.'));
	echo '<p>';
	echo '<div><label><input type="radio" name="disable_mobile" value="show_large"';
	if (isset($this_skin['disable_mobile']) &&
		trim($this_skin['disable_mobile']) == 'show_large' ||
		isset($this_skin['disable_mobile']) &&
		trim($this_skin['disable_mobile']) == 'yes')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Only show on large or medium-sized devices</label></div>';
	echo '<div style="margin-top:10px;"><label><input type="radio" name="disable_mobile" value="show_small"';
	if (empty($this_skin['disable_mobile']) ||
		trim($this_skin['disable_mobile']) == 'show_small')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Only show small devices</label></div>';
	echo '<div style="margin-top:10px;"><label><input type="radio" name="disable_mobile" value="show_all"';
	if (empty($this_skin['disable_mobile']) ||
		trim($this_skin['disable_mobile']) == 'show_all' ||
		trim($this_skin['disable_mobile']) == '')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Show on all devices</label></div>';
	echo '</p>';


	ois_end_option_and_table();
	ois_end_option_and_table(); // end positioning section.

	//echo '</table>'; // ends the positioning section.
	ois_start_table('Split-Testing', 'mantra/Clock.png');
	ois_option_label(array(
			'title' => 'Are you a perfectionist?',
			'description' => 'Find out which design or message speaks to your readers best by comparison.',
			'inner_style' => 'width:320px;' ));
	ois_inner_label(array('title' => 'Split-Test This Skin'));
	echo '<p><input type="checkbox" name="split_testing" value="yes"';
	if (isset($this_skin['split_testing']) &&
		trim($this_skin['split_testing']) == 'yes')
	{
		echo 'checked="checked"';
	} // if
	echo '/> Enable Split-Testing</p>';
	echo '<p style="color: #666;">When you enable split-testing for two skins, and you assign them to the same position, only one will appear per pageview.<br/>You can compare their performances in the \'Split-Testing\' section in the OptinSkin menu.</p>';
	ois_end_option_and_table();
	ois_end_option_and_table();

	if (isset($this_skin['aff_username'])
		&& trim($this_skin['aff_username']) != '')
	{
		$aff_username = $this_skin['aff_username'];
	} // if
	else
	{
		$aff_username = get_option('ois_aff_user');
	} // else

	if (isset($this_skin['aff_enable']))
	{
		$aff_enable = $this_skin['aff_enable'];
	} // if
	else
	{
		$aff_enable = 'no';
	} // else

	ois_start_table('Affiliate Options', 'mantra/ID.png');
	ois_option_label(array(
			'title' => 'Want to Make Money?',
			'description' => 'Use your skin to sell OptinSkin as an affiliate, and earn more money from your website.',
			'inner_style' => 'width:320px;' ));
	echo '<img style="float:right;width: 140px; margin-right:40px; padding: 15px;" src="' . OIS_URL . 'admin/images/clickbank.png" />';
	ois_inner_label(array('title' => 'Clickbank Username'));
	echo '<p><input	type="text"
					class="ois_textbox"
					name="aff_user"
					placeholder="Affliate Username"
					value="' . $aff_username . '" /></p>';
	ois_end_option_and_table();
	ois_inner_label(array('title' => 'Enable Affiliate Link for this Skin'));
	echo '<p><input	type="checkbox"
						name="aff_enable"
						value="yes"';
	if ($aff_enable == 'yes') {
		echo 'checked="checked"';
	}
	echo '/> Enable
			<p>Disabling this option will remove the link from your skin.</p></p>';
	ois_end_option_and_table();
	ois_end_option_and_table();

	ois_start_table('Finalize Your Skin', 'mantra/Upload.png');
	ois_option_label(array(
			'title' => 'Save Data',
			'description' => 'When you are finished creating your skin, hit \'Add this Skin\'.' ));
?>
					<input 	type="hidden"
							name="newskin_design_section"
							id="newskin_design_selection"
							<?php
	if (!empty($this_skin)) {
		echo 'value="' . $this_skin['design'] . '" />';
	} else {
		echo 'value="1" />';
	}
?>
					<input 	type="hidden"
							name="newskin_status"
							id="newskin_status"
							value="publish" />

				<?php  if (!empty($skin_id) && trim($skin_id) != '') { ?>
					<input 	type="hidden"
							name="current_skin"
							id="newskin_current_skin"
							value="<?php echo $skin_id; ?>" />
					<?php
	}
?>
					<div style="text-align:center; margin-right:300px;">
		<?php
	if (isset($this_skin['status']))
	{
		ois_super_button(array('value'=>'Update Skin'));
	} // if
	else // status will only be available if it has been saved before. Make sense?
		{
		ois_super_button(array('value'=>'Create this Skin', 'style' => 'color: white; text-shadow: none;background-color: #35aa47;background-image: -webkit-linear-gradient(top,#35aa47,#35aa47);-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;border: 0;font-size:16px;font-weight: normal;padding: 10px 15px;height: auto;'));
	} // else
	wp_nonce_field('ois_add_field', 'save_data');
	ois_end_option_and_table();
	echo '</form>';
	ois_section_end();

	/*
		LOADING GIF
	*/
?>
	<div id="ois_add_loader" style="display:none">
		<div style="margin-left:100px;margin-top:20px;margin-bottom:20px;">
		<h2 style="padding-bottom:10px;">Loading design</h2>
		</div>
	</div>
	<?php
	
	
}
?>