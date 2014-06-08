<?php
add_action( 'admin_menu', 'ois_admin_actions' );


/**
 * ois_admin_actions function.
 * Set up the admin menu for OptinSkin. 
 *
 * @access public
 * @return void
 */
function ois_admin_actions() {
	// Create Option for General Settings
	$validated = get_option('ois-valid');
	if (trim($validated) != 'yes') 
	{
		// License Key options, so that the user can change his or her license.
		add_menu_page('License Key', 'OptinSkin', 'manage_options', 'ois-license-key', 'ois_license_key', WP_PLUGIN_URL . '/OptinSkin/admin/images/icon.png' );
	} // if
	else
	{
		// Create menu
		add_menu_page( 'OptinSkin', 'OptinSkin', 'manage_options', 'addskin', 'ois_add_new', WP_PLUGIN_URL . '/OptinSkin/admin/images/icon.png' );
		// Create Option for Creating New OptInSkins
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page( 'addskin', 'Add New', 'Add New', 'manage_options', 'addskin', 'ois_add_new' );

		// Create Options for All Existing OptINSkins
		$existing_skins = get_option( 'ois_skins' );

		$stats_disable = get_option('stats_disable');

		if (!empty($existing_skins)) {
			foreach ( $existing_skins as $skin_id => $skin ) {
				// Extract data about skin.
				$skin_name = $skin['title'];
				// but only add a page if this is published.
				if ($skin['status'] == 'publish') 
				{
					// Create the option page for this specific existing skin.
				add_submenu_page( 'addskin', $skin_name, $skin_name, 
					'manage_options', 'ois-' . $skin_id, 'ois_setup_edit_skin' );
				}  // if
			} // foreach
		} // if
		
		// Option to export skins
		add_submenu_page( 'addskin', 'Export Skins', 'Export Skins', 'manage_options', 'oisexport', 'ois_export_skins' );
    
    // Custom Designs
		add_submenu_page( 'addskin', 'Custom Designs', 'Custom Designs', 'manage_options', 'create-design', 'ois_custom' );
		
		// Split-Testing Stats
		if ($stats_disable != 'yes') 
		{
			add_submenu_page( 'addskin', 'Compare Skins', 'Compare Skins', 'manage_options', 'ois-split-testing', 'ois_statistics' );
		} // if
		
		// Top Posts
		add_submenu_page( 'addskin', 'Top Posts', 'Top Posts', 'manage_options', 'ois-top-posts', 'ois_setup_top_posts_stats' );
		
		// License Key options, so that the user can change his or her license.
		add_submenu_page( 'addskin', 'License Key', 'License Key', 'manage_options', 'ois-license-key', 'ois_license_key' );
		
		// Support
		add_submenu_page( 'addskin', 'Support', 'Support', 'manage_options', 'ois-support', 'ois_setup_support' );
				
		add_submenu_page( 'addskin', 'General Settings', 'General Settings', 'manage_options', 'optinskin-settings', 'ois_setup_general_settings' );

		$error_cats = get_option('ois_error_log');
		$num_errors = 0;
		if (!empty($error_cats)) {
			foreach ($error_cats as $cat) {
				$num_errors += count($cat);
			}
		}
		if ($num_errors > 0) {
			add_submenu_page( 'addskin', 'Error Log (' . $num_errors . ')', 'Error Log (' . $num_errors . ')', 'manage_options', 'ois-error-log', 'ois_error_log' );
		}
	}
}

function ois_setup_support() {
  include_once dirname(__FILE__) . '/../support/admin_support.php'; // allows user to change settings
  ois_support();
} // ois_support()

function ois_setup_top_posts_stats () {
  include_once dirname(__FILE__) . '/../topPosts/top_posts.php'; // allows user to change settings
  ois_top_posts();
}

function ois_setup_general_settings() {
  include_once dirname(__FILE__) . '/../admin_settings.php'; // allows user to change settings
  ois_general_settings();
} // ois_setup_general_settings ()

function ois_dash() {
	ois_section_title('OptinSkin Dashboard', 'Review Your OptinSkin Usage');

?>
	<style type="text/css">
		.ois_dash_question {
			font-weight: bold;
			margin-right: 5px;
		}
		.ois_dash_answer {

		}
	</style>
	<table class="widefat">
		<thead>
			<th>Overview</th>
			<th></th>
		</thead>
		<tr>
			<td>

			</td>
		</tr>
		<tr>
			<td>
				<span class="ois_dash_question">Published Skins:</span>
				<span class="ois_dash_answer">5</span>
			</td>
			<td>

			</td>
		</tr>
	</table>
	<?php
}

function ois_add_designs () {
	// this should actually get the data straight from the optinskin.com website, but if cURL isn't functional,
	// then obviously just use the styles that came with the plugin.
	$all_designs = get_option('ois_all_designs');
	$included_designs = get_option('ois_designs');

	if (isset($_GET['id'])) {
		if (check_admin_referer('add_design')) {

			foreach ($all_designs as $design) {
				if ($design['id'] == $_GET['id']) {
					$id = (1 + count($included_designs));
					$included_designs[$id] = $design;
					update_option('ois_designs', $included_designs);
				}
			}

			ois_notification('A new design has been added!', '', '');
		}
	}

	ois_section_title('Add Designs', 'Add Designs to Use in Your Skins', '');


	echo '<table class="widefat">';
	echo '<thead>
		<th>Design Preview</th>
		<th style="width:150px;">Add Designs</th>
		</thead>';
	echo '<tbody>';
	if (!empty($all_designs)) {
		foreach ($all_designs as $design) {
			if (!in_array($design, $included_designs)) {
				ois_design_row($design);
			} else { // I want this to have 'update' instead of add
				//echo '<tr><td>already here</td><td></td></tr>';
			}
		}
	}
	echo '</tbody>
		</table>';
	ois_section_end();

}

function ois_design_row ($design) {

	$inc_des = array();
	if (!empty($included_designs)) {
		foreach ($included_designs as $inc) {
			array_push($inc_designs, $inc['design_id']);
		}
	}
	if (in_array($design['design_id'], $inc_des)) {
		// This is an update
		$update = true;
	} else {
		$update = false;
	}

	if (isset($design['title'])) {
		$design_name = $design['title'];
	} else {
		$design_name = 'Untitled';
	}
	if (isset($design['id'])) {
		$design_id = $design['id'];
	} else {
		$design_id = 'unknown';
	}

	$uri = explode('?', $_SERVER['REQUEST_URI']);
	$design_adding_url = $uri[0] . '?page=ois-add-designs';
	$design_adding_url = wp_nonce_url($design_adding_url, 'add_design') . '&id=' . $design_id;
	$design_updating_url = wp_nonce_url($design_adding_url, 'add_design') . '&update=' . $design_id;

	echo '<tr>';
	echo '<th class="alternate">';
	echo '<div class="ois_preview_header" ></div>';
	if (isset($design['design_preview'])
		&& trim($design['design_preview'] != '')) {
		echo '<img src="' . $design['design_preview'] . '" />';
	}
	echo '</th>';
	if ($update === true) {
		echo '<td class="alternate">' . '<a class="ois_styled_button" style="margin:15px 0 !important;' . ois_vertical_gradient('#feda71', '#febf4f') . '" ';
		echo 'href="' . $design_updating_url . '">';
		echo 'Update Design</a>' . '</td>';
		echo '</tr>';
	} else {
		echo '<td class="alternate">' . '<a class="ois_styled_button" style="margin:5px 0 !important;' . ois_vertical_gradient('#feda71', '#febf4f') . '" ';
		echo 'href="' . $design_adding_url . '">';
		echo 'Click to Add</a>' . '</td>';
		echo '</tr>';
	}

}

function ois_setup_edit_skin() {
	$page_token = explode('-', $_GET['page']);
	if (count($page_token) > 1) {
  	include_once(dirname(__FILE__) . "/../skinStats/admin_skin_stats.php"); // shows skin statistics
		$skin_id = $page_token[1];
		$skins = get_option('ois_skins');
		$skin = $skins[$skin_id];
		ois_edit_skin($skin);
	} 
	else 
	{
		'<p>Sorry, no such skin exists.</p>';
	}
}
?>