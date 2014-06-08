<?php


/**
 * ois_remove_skin_stats function.
 *
 * deletes all of the stats for a given skin
 * 
 * @pre table such as WP_optinskin must have been created
 * @post all rows of this table that have the skin_id given will be deleted
 * @access public
 * @param mixed $skin_id
 * @return void
 */
function ois_remove_skin_stats($skin_id) {
  // Delete all stats where skin_id = $skin_id
  global $wpdb;
  $table_name = $wpdb->prefix . 'optinskin';
  $row = $wpdb->delete(
      $table_name, array(
          'skin' => $skin_id,
      ) // table_name
  ); // insert
} // ois_remove_skin_stats


/**
 * ois_print_top_ten_area function.
 * 
 * prints a table for the top ten post data, which filled in by javascript code
 * 
 * @access public
 * @return void
 */
function ois_print_top_ten_area() {
  include dirname(__FILE__) . '/views/top_ten.html';
} // ois_print_top_ten_area()

/**
 * ois_print_nav_and_custom_info function.
 * 
 * prints a menu for the skin, and information about including in custom locations.
 * 
 * @access public
 * @param mixed $skin_id
 * @return void
 */
function ois_print_nav_and_custom_info ($skin_id) {
  // Create the urls for the navbar
  $request_uri = $_SERVER['REQUEST_URI'];
  $uri = explode('?', $request_uri);
  $urls = array (
    'current' => $uri[0] . "?page=ois-$skin_id",
	  'edit' => $uri[0] . "?page=addskin&id=$skin_id",
	  'duplicate' => $uri[0] . "?page=addskin&duplicate=$skin_id",
	  'export' => $uri[0] . "?page=oisexport&skin=$skin_id",
	  'trash' => wp_nonce_url( $request_uri , 'trash' ) . "&delete=$skin_id",
	  'clear-stats' => wp_nonce_url( $request_uri , 'clear-stats' ) . "&clear=$skin_id",
	);
	// Include the glyphicons that are used by these views
	echo '<link href="' . OIS_URL . 'admin/css/glyphicons.bootstrap.min.css" rel="stylesheet" />';
  // Include the appropriate view
  include dirname(__FILE__) . '/views/nav_and_custom_info.php';
} // ois_print_nav_and_custom_info(String skin_id)

/**
 * ois_print_no_stats_message function.
 * 
 *  Prints a message when the user has no stats
 * @access public
 * @return void
 */
function ois_print_no_stats_message () {
  include dirname(__FILE__) . '/views/no_stats.html';
} // ois_print_no_stats_message

/**
 * ois_print_stats_display_area function.
 * 
 * Prints the area where stats will be shown
 * @access public
 * @return void
 */
function ois_print_stats_display_area() {
  include dirname(__FILE__) . '/views/stats_display.html';
} // ois_print_stats_display_area


/**
 * ois_print_stats function.
 * 
 * @post prints visualizations of statistics, if any are available
 *
 * @access public
 * @return void
 */
function ois_print_stats ($skin_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'optinskin';
  
  // First of all, let's get the impressions data.
  // The SQL code looks like this:
  // Get the date, but not the minutes, seconds, that come with timestamp
  $sql = "SELECT SUBSTRING(ts FROM 1 FOR 10) AS 'date'," .
    // Also get the count, and the type (impression or submission)
    "count(ts) AS 'count',submission " .
    // Get data for this skin
    "FROM $table_name WHERE skin='$skin_id' " .
    // The data must come only from the last 30 days
    "AND (ts > DATE_SUB(now(), INTERVAL 30 DAY)) " . 
    // Group them by the date, and divide by submission type
    "GROUP BY SUBSTRING(ts FROM 1 FOR 10),submission;";
    // Ordering will be done by JavaScript function (because JSONs are unordered)
    
  $rows = $wpdb->get_results($sql);
  // Now we have an array of objects that look like: { date: x-x-x, count: x }
  // E.g. echo (json_encode($rows)) = [{"date":"2014-06-05","count":"5"}]
  $impressions_as_json = '';
  $submissions_as_json = '';
  
  // Check to see if there are data available for visualizations
  if (empty($rows)) {
    // There is no data available yet.
    ois_print_no_stats_message();
  } else {
      
      
    foreach ($rows as $row) {
      // If submission, put into submission array, else into impressions.
      if ($row->submission === '1' && (!empty($row->count))) {
        $submissions_as_json .= "'" . $row->date . "':" . $row->count . ',';
      } else {
        // It ought to be true that all dates have at least one impression.
        $impressions_as_json .= "'" . $row->date . "':" . $row->count . ',';
      } // else 
    } // foreach
  
    // Remove the last comma in arrays
    $impressions_as_json = substr($impressions_as_json, 0, strlen($impressions_as_json) - 1);
    $submissions_as_json = substr($submissions_as_json, 0, strlen($submissions_as_json) - 1);
    // The visualizations need impression and submission data, print those.
    ?>
      <script type="text/javascript">
        var impressionData = {<?php 
          echo $impressions_as_json
        ?>};
        var submissionData = {<?php
          echo $submissions_as_json;
        ?>};
      </script>
    <?php
    // Print the various display areas
    ois_print_stats_display_area();
    //ois_print_top_ten_area();
    
    // Include any scripts needed for the graphs
    ois_include_d3_scripts();
    
    // Include the script for displaying the visualizations.
    $script_url = OIS_URL . "admin/skinStats/js/script.js";
    
    echo "<script type='text/javascript' src='$script_url'></script>";
    
    
    
    
  } // else
} // ois_print_stats(Array, Array)

function ois_delete_skin_and_redirect($skin_id) {
    // Get the array of all skins, remove this one.		  
		$all_skins = get_option('ois_skins');
		$skin_id = $_GET['delete'];
		unset($all_skins[$skin_id]);
		// Save this updated array of skins
		update_option('ois_skins', $all_skins);
		
		// Delete all stats for this skin
		ois_remove_skin_stats($skin_id);
    
		// Delete content from skins directory (contains the saved html files)
		$skin_path = OIS_PATH . "/skins/$skin_id";
		ois_recursive_rmdir($skin_path);
		
		// Redirect the user, and display a message.
		$updated_message = '&update=delete';
		$cur_location = explode("?", $_SERVER['REQUEST_URI']);
		$new_location = 'http://' . 
			$_SERVER["HTTP_HOST"] . $cur_location[0] . '?page=addskin';
		echo '<script type="text/javascript">
				window.location = "' . $new_location . $updated_message . '";
		</script>';
} // ois_delete_skin_and_redirect(String)

function ois_edit_skin($skin) {
  
  $skin_id = $skin['id'];
  
	if (isset($_GET['delete'])) {
		if (check_admin_referer('trash')) 
		{
		  ois_delete_skin_and_redirect($skin_id);
		} // if
	} // if delete
  else  {		
    // Here are the things that require this page to load, but are mutating as well.
    // Removing statistics
    if (isset($_GET['clear'])) {
	    if (check_admin_referer('clear-stats')) {
        ois_remove_skin_stats($_GET['clear']);
        ois_notification('The stats for this skin have been successfully cleared!', 'margin: 5px 0 0 0 ;', '');
      } // if
    } // if
    // Updating skin settings
		if (isset($_GET['updated']) && $_GET['updated'] == 'true') 
		{
			ois_notification('Successfully Updated Your Skin!', 'margin: 5px 0 0 0 ;', '');
		} // if
		
		else if (isset($_GET['created']) && $_GET['created'] == 'true') 
		{
				$uri = explode('?', $_SERVER['REQUEST_URI']);
				$stats_url = $uri[0] . '?page=stats';
				ois_notification('Your new skin is now live on your site.' .
					'If you enabled split-testing, you can view how it is performing <a href="' .
					 $stats_url . '">here</a>.', 'margin: 5px 0 0 0 ;', '');
		} // else if
			
		ois_section_title('Skin Performance',  stripslashes($skin['title']), '');
		
		// Print the menu at the top and the custom info section
		ois_print_nav_and_custom_info($skin_id);
      // Print the vizualizations, if there are statistics
    ois_print_stats ($skin_id);
       
  } // else (not deleting skin)
  ois_section_end();
} // ois_edit_skin(Array)
?>