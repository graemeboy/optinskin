<?php

function ois_editing_heading($skin_id, $skin_name, $status)
{
	// Title
	$subtitle = "You are Currently Editing <em>$skin_name</em></em>";
	ois_section_title('Edit Skin', $subtitle);
	
	
	$uri = explode('?', $_SERVER['REQUEST_URI']);
	$dup_url = $uri[0] . '?page=addskin&duplicate=' . $skin_id;
?>
<div>
	<h2 class="nav-tab-wrapper">
	<?php 
	if ($status == 'publish')
	{
		// If the skin has been published, then the user can check performance.
		$performance_url = $uri[0] . '?page=ois-' . $skin_id;
		?>
		<a href="<?php echo $performance_url; ?>" class="nav-tab"><span class="glyphicon glyphicon-signal"></span> Skin Performance</a>
		<?php
	} // if
	?>
		<a href="<?php echo $_SERVER['REQUEST_URI']; ?>" class="nav-tab-active nav-tab"><span class="glyphicon glyphicon-edit"></span> Edit Skin</a>
		<a href="<?php echo $dup_url; ?>" class="nav-tab"><span class="glyphicon glyphicon-plus"></span> Duplicate Skin</a>
	</h2>
</div>	
	<?php
} // ois_add_heading (boolean)

function ois_generate_new_id($all_skins)
{
	// To create a new skin id, we cannot just count and add 1, because skins can be removed.
	$skin_id = 1;
	while (isset($all_skins[$skin_id]))
	{
		$skin_id++;
	} // while
	
	return $skin_id;
} // ois_generate_new_id (array)

function ois_add_init_table($skin_title, $skin_desc)
{
	ois_start_option_table('Initialize Your Skin', true, 'mantra/Comments.png');

	$data = array(
		'title' => 'Skin Name',
		'description' => 'The title used to identity this skin.',
		'alternative' => 'yes',
	);
	ois_option_label($data); ?>

	<input type="text" class="ois_textbox" id="ois_skin_name" name="newskin_name" placeholder="New Skin Name" value="<?php echo $skin_title; ?>" />
	<?php
	$random_messages = array( 'Great name!', 'That will do!', 'Excellent!', 'A splendid name!');
	$message = $random_messages[array_rand($random_messages)];
	ois_validate_message( array(
			'text' => $message,
			'value' => 'approve',
			'show' => false,
			'id' => 'ois_name_approve'));
	ois_validate_message( array(
			'text' => 'Please name your skin',
			'value' => 'disapprove',
			'show' => false,
			'id' => 'ois_name_disapprove'));
	ois_option_end();

	/*
$data = array(
		'title' => 'Skin Purpose',
		'description' => 'Briefly describe your outcome for this skin.',
	);
	ois_option_label($data);
?>
	<input type="text" class="ois_textbox" id="new_skin_description" name="newskin_description" placeholder="The reason I am creating this skin is" value="<?php echo $skin_desc; ?>" /><br/>
	<?php
	ois_validate_message( array(
			'text' => 'Awesome. Having a description for your skin will keep you focused on its aim.',
			'value' => 'approve',
			'show' => false,
			'id' => 'ois_description_approve',
			'paragraph' => true));
	ois_option_end();
*/
	ois_table_end();
} // ois_add_init_table()


function mailchimp_options ($settings, $optin_accounts) { 
  
  $service = 'mailchimp';
  
  if (!empty($settings['mailchimp_type'])) {
    $type = $settings['mailchimp_type'];
  } else {
    // Default type is API
    $type = 'api';
  }
  ?>
  <div style="margin-bottom:15px;">
    <span style="margin-right:15px">
    <input type="radio" class="ois-mailchimp-toggle" <?php
      if ($type == 'api') {
        echo 'checked="checked"'; 
      }
    ?> id="ois-mailchimp-api" name="mailchimp_type" value="api"> Use API
    </span>
    <span>
    <input type="radio" class="ois-mailchimp-toggle" <?php
      if ($type == 'code') {
        echo 'checked="checked"'; 
      }
    ?> id="ois-mailchimp-code" name="mailchimp_type" value="code"> Use Naked Form Code
    </span>
  </div>
  
    <div id="ois_mailchimp_api_options" <?php if ($type == 'code') { 
      echo 'style="display:none;margin-bottom:10px"';
    } ?>>
        <?php
        ois_inner_label(array('title' => 'API Key<br/><small>http://admin.mailchimp.com/account/api/</small>', 'inner_style' => 'vertical-align: bottom !important;')); // opens table, tr, td
        optin_simple_text($settings, $optin_accounts, 'mailchimp_api_key', 'mailchimp-api');
        ois_end_option_and_table(); // closes td, tr, table
        
        ois_inner_label(array('title' => 'List ID<br/><small>http://admin.mailchimp.com/lists/
<br/>Click the "settings" link for the list</small>', 'inner_style' => 'vertical-align: bottom !important;'));
        optin_simple_text($settings, $optin_accounts, 'mailchimp_list_id', 'mailchimp-api');
        ois_end_option_and_table();
        
        ?><div style="margin-bottom:10px"></div><?php
        
        redirect_url_input ($service, $settings);
        ois_end_option_and_table();
        
        ?><div style="margin-bottom:10px"></div><?php
        
        ?>
    </div>
    <div id="ois_mailchimp_code_options" <?php if ($type == 'api') { 
      echo 'style="display:none"';
    } ?>>
    <?php
    $item = 'mailchimp-form';
    $name = 'mailchimp-code';
    ois_inner_label(array('title' => 'Naked Form HTML'));
    optin_html_text($settings, $optin_accounts, $item, $name);
		ois_option_end();
    ?>
    </div>
    
    <script type="text/javascript">
      jQuery(document).ready(function ($) {
        $('.ois-mailchimp-toggle').change(function () {
          var api = $('#ois-mailchimp-api').is(":checked");
          console.log(api);
          if (api === true) {
            console.log('api is true');
            $('#ois_mailchimp_code_options').hide();
            $('#ois_mailchimp_api_options').show();
          } else {
            $('#ois_mailchimp_api_options').hide();
            $('#ois_mailchimp_code_options').show();
          } // else
        });
        
      });
    </script>
  <?php
} // mailchimp_options (Array)

function aweber_options ($settings, $optin_accounts) {
  
  $service = 'aweber';
  
  ois_inner_label(array('title' => 'List Name (e.g. \'viperchill\')'));
  
  $item = 'aweber-id';
  
  optin_simple_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
  redirect_url_input ($service, $settings);
  
} // aweber_options (Array)

function feedburner_options($settings, $optin_accounts) {
  $service = 'feedburner';
  
  ois_inner_label(array('title' => 'Feedburner ID'));
  $item = 'feedburner-id';
  
  optin_simple_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
  redirect_url_input ($service, $settings);
  
} // feedburner_options (Array, Array)

function getresponse_options($settings, $optin_accounts) {
  $service = 'getResponse';
  
  ois_inner_label(array('title' => 'Webform ID'));
  $item = 'getResponse-id';
  optin_simple_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
  
  /*
    There is also an option here to use the html...
    'getResponse' => array (
  			'Webform ID' => 'getResponse-id',
  			'Form HTML' => 'getResponse-html',
  		),
  */
  
} // getresponse_options(Array, Array)

function custom_options($settings, $optin_accounts) {
  $service = 'custom';
  
  ois_inner_label(array('title' => 'Form Action<br/><small>E.g. http://www.aweber.com/scripts/addlead.pl</small>', 'inner_style' => 'vertical-align: middle !important;'));
  $item = 'custom_action';
  optin_simple_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
  ois_inner_label(array('title' => 'Email name-value<br/><small>E.g. EMAIL</small>', 'inner_style' => 'vertical-align: middle !important;'));
  $item = 'custom_email';
  optin_simple_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
  ois_inner_label(array('title' => 'Name name-value (Optional)<br/><small>E.g. FNAME</small>', 'inner_style' => 'vertical-align: middle !important;'));
  $item = 'custom_name';
  optin_simple_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
  
} // custom_options(Array, Array)

function other_options($settings, $optin_accounts) {
  $service = 'other';
  
  ois_inner_label(array('title' => 'Form HTML'));
  $item = 'other-html';
  optin_html_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
} // other_options (Array, Array)

function infusionsoft_options($settings, $optin_accounts) {
  $service = 'infusionSoft';
  
  ois_inner_label(array('title' => 'Form HTML'));
  $item = 'infusionSoft-html';
  optin_html_text($settings, $optin_accounts, $item, $service);
  ois_option_end();
  
} // infusionsoft_options(Array, Array)


function optin_html_text($settings, $optin_accounts, $item, $name) {
  echo '<textarea type="text"
			style="width:500px; height: 200px;"
			class="ois_add_appearance ois_textbox ois_optin_account_input"
			name="newskin_' . $item . '"
			account="' . $name . '" >';
		if (!empty($this_skin['optin_settings'][str_replace('-', '_', $item)]))
		{
			$potential_val = trim($this_skin['optin_settings'][str_replace('-', '_', $item)]);
		} else
		{
			$potential_val = '';
		} // else
		if ($potential_val != '')
		{
			echo stripslashes($potential_val);
		} else {
			if (!empty($optin_accounts[$item]))
			{
				$potential_val = $optin_accounts[$item];
			} else
			{
				$potential_val = '';
			}
			if (trim($potential_val) != '')
			{
				echo stripslashes($potential_val);
			}
		} // else
		echo '</textarea>';
  
}

function optin_simple_text($settings, $optin_accounts, $item, $name) {
  echo '<input type="text"
				style="width:200px;"
				class="ois_textbox ois_optin_account_input"
				name="' . $item . '"
				account="' . $name . '"';
				if (!empty($settings[$item])
					&& trim($settings[$item]) != '') {
					$potential_val = trim($settings[$item]);
				} else {
					$potential_val = '';
				}
				if ($potential_val != '') {
					echo 'value="' . $potential_val . '"';
				} else {
					if (!empty($optin_accounts[$item])) {
						$potential_val = $optin_accounts[$item];
					} else {
					  // try to get it from the settings
					  $saved_value = get_option('ois_' . $item);
					  // if get_option doesn't exit, it returns ''
						$potential_val = $saved_value; // saved value or ''
					}
					if (trim($potential_val) != '') {
						echo 'value="' . $potential_val . '"';
					}
				} // else
  echo '/>';
  
}

function redirect_url_input ($optin, $settings) {
  //ois_option_label(array('title' => 'Redirect Option', 'description' => 'Where will users go after they have subscribed?<br/><br/>Leave blank for no redirect.'));
	ois_inner_label(array('title' => 'Full Redirect URL',
			'description' => 'Where do users go after they have subscribed?'));

	if (!empty($settings['redirect_url']))
	{
		$redirect_url = $settings['redirect_url'];
	} // if
	else
	{
		$redirect_url = '';
	} // else
?>
			<input type="text" class="ois_textbox ois_redirect_url" name="redirect_url_<?php echo $optin ?>" style="width:420px;" value="<?php echo $redirect_url ?>" />
			<select class="ois_select_page">
			<option>Select from all Pages</option>
			<?php
	$pages = get_pages();
	foreach ( $pages as $pagg ) {
		$option = '<option value="' . get_page_link( $pagg->ID ) . '">';
		$option .= $pagg->post_title;
		$option .= '</option>';
		echo $option;
	}
	?></select>

	<?php
	ois_option_end();
}
?>