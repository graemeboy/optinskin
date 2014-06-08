<?php
//===================================================
//! admin_validation.php
// Allows the user to update his or her license key.
//===================================================

/*
    Note: Please do not edit this page, unless you really know what you're doing.
    If you try to override the license-key that is used, the plugin will probably break.
    The license key is required to be sent to our server, to receive latest designs.
*/

// AJAX FOR LICENSE KEY VALIDATION
add_action( 'wp_ajax_ois_update_license', 'ois_update_license' );

/**
 * ois_license_key function.
 * Allows the user to update and validate his or her license key.
 *
 * @access public
 * @return void
 */
function ois_license_key()
{
	// Add this section's title.
	ois_section_title('License Key', 'Here you can update your license key settings', '');
	
	// Get the current license key.
	$license_key = get_option('ois-key');
	// Try to determine the home URL; necessary for single-site licenses.
	$home_url = home_url();
	if ($home_url == '')
	{
		$home_url = 'Undefined';
	} // if
?>
<link href="<?php echo OIS_URL ?>admin/validation/css/style.css" rel="stylesheet">

<div id="validation-error" class="ois-validation-alert">Error</div>
<div id="validation-success" class="ois-validation-alert">Success</div>

<h4>Please enter your license key below. The plugin requires this to access the designs when you create a new skin.</h4>

<p>You can find your license key when you log in at <a target="_blank" href="http://www.optinskin.com/members">http://www.optinskin.com/members</a></p>

<form method="post" id="ois-validation-form">
	<input type="hidden" name="homeUrl" value="<?php echo $home_url; ?>" />
	<input type="hidden" name="validate" value="yes" />
	<div>
		<input type="text" id="ois-license-in" class="ois_textbox" style="width:230px;" placeholder="Type your license key here" name="licenseKey" value="<?php echo $license_key ?>">
	</div>
	<div>
		<input type="submit" class="ois_super_button" value="Save Settings">
	</div>
</form>
<script type="text/javascript">
	jQuery(document).ready(function ($)
	{
		var data;
		var apiUrl = "<?php echo OIS_EXT_URL ?>check_license.php";
		var license = "";
		
		$('#ois-validation-form').submit(function (e)
		{
			e.preventDefault();
			$('.ois_super_button').attr('disabled', 'disabled');
			$('.ois_super_button').val('Saving...');
			
			license = $("#ois-license-in").val();
			// We will be expecting a new alert, so hide any current.
			$('.ois-validation-alert').hide();
			// Send to external server.
			data = $('#ois-validation-form').serialize();
			jQuery.post(apiUrl, data, function (resp)
			{
				// This is going to check to see if we have a working license.
				if (resp == 1)
				{
					// Good.
					var data = {
						action: 'ois_update_license',
						license: license,
						homeUrl: "<?php echo $home_url ?>",
					};
					// Send to Wordpress to update options.
					// ajaxurl is already defined.
					$.post(ajaxurl, data, function(response) {
						if (response == 0)
						{
							$('#validation-error').text(
								"Your license key was validated, but there " +
								"seems to be a problem with updating the option on Wordpress. " +
								"Please try again, and if the problem persists, " +
								"contact OptinSkin support.");
								
							$('#validation-error').show();
						} // if 
						else
						{
							$('#validation-success').html(response);
							// Create a link for the user to begin adding skins.
							var curURL = document.URL.split('?');
							var addingURL = curURL[0] + '?page=addskin';
							$('#ois-start-adding').attr('href', addingURL);
							$('#validation-success').show();
						} // else
						$('.ois_super_button').removeAttr('disabled');
						$('.ois_super_button').val('Save Settings');
						//alert('Got this from the server: ' + response);
					});
				} // if
				else
				{
					// Error.
					$('#validation-error').text(resp);
					$('#validation-error').show();
					$('.ois_super_button').removeAttr('disabled');
					$('.ois_super_button').val('Save Settings');
				} // else
			}) // done
			.fail(function ()
			{
				// Error.
					$('#validation-error').text("There was trouble connecting to our validation server. Please try again.");
					$('#validation-error').show();
			}); //fail
		}); // submit
	}); // document. ready
</script>
<?php
	//}
}

/**
 * ois_update_license function.
 * Update the license once successfully posted via ajax.
 * 
 * @access public
 * @return void
 */
function ois_update_license ()
{
	if (isset($_POST['license']))
	{
		$license = $_POST['license'];
		if (isset($_POST['homeUrl']))
		{
			$home_url = $_POST['homeUrl'];
		} // if
		else
		{
			// But generally, there should be. This is in rare case.
			$home_url = 'Undefined';
		} // else no home url
		update_option('ois-valid', 'yes');
		update_option('ois-home-url', $home_url);
		update_option('ois-key', $license);
				
				
		echo "Thank you for using OptinSkin. " . 
			"Your license key was validated, and saved successfully." . 
			'<div style="margin-top:5px;"><a href="#" id="ois-start-adding" ' . 
				'style="cursor:pointer;text-decoration:none;">' . 
					'Click here to begin creating skins.</a></div>';
	} // if
	else
	{
		// There was an error updating.
		echo 0;
	} // else
	
	die();
}
?>