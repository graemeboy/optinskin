<?php

function ois_top_posts() {
  
  $num_items_in_graph = 10;
  
  // Include the glyphicons that are used by these views
	echo '<link href="' . OIS_URL . 'admin/css/glyphicons.bootstrap.min.css" rel="stylesheet" />';
	
  ois_section_title('Top Post Performance', 'See which of your posts are converting best');
  

	global $wpdb;
  $table_name = $wpdb->prefix . 'optinskin';
  
  // First of all, let's get the impressions data.
  // The SQL code looks like this:
  // Get the date, but not the minutes, seconds, that come with timestamp
  $sql = "SELECT count(submission) AS 'count',submission,post " .
    "FROM $table_name " .
    // The data must come only from the last 30 days
    "WHERE (ts > DATE_SUB(now(), INTERVAL 30 DAY)) " . 
    // Group them by the post, and divide by submission type
    "GROUP BY post,submission;";
    // Ordering will be done by JavaScript function (because JSONs are unordered)
  
  // Note, this may have returned a lot of rows - for for every post that has a
  // submission on it!
  
  $rows = $wpdb->get_results($sql);
  
  //print_r($rows);
  // Now we have an array of objects that look like: { date: x-x-x, count: x }
  // E.g. echo (json_encode($rows)) = [{"date":"2014-06-05","count":"5"}]
  $impressions_as_json = '';
  $submissions_as_json = '';
  $links_json = '';

  $impressions = array();
  $submissions = array();  
  $conversion_rates = array();
  $finalData = array();

  // Check to see if there are data available for visualizations
  if (empty($rows)) {
    // There are no data available yet.
    echo "There are no data available yet";
  } else {
    foreach ($rows as $row) {
      // If submission, put into submission array, else into impressions.
      if ($row->submission === '1' && (!empty($row->count))) {
        $submissions[$row->post] = $row->count;
      } else {
        // It ought to be true that all dates have at least one impression.
        $impressions[$row->post] = $row->count;
      } // else 
    } // foreach
    
    foreach ($submissions as $post=>$count) {
      if (isset($impressions[$post])) {
        // Get precentile rate, to one decimal
        $rate = round(($count / $impressions[$post]), 3);
      } else {
        $rate = 0;
      } // else
      $conversion_rates[$post] = $rate;
    } // foreach
    
    foreach ($conversion_rates as $post=>$rate) {
      $link = get_the_permalink($post);
      $title = get_the_title($post);
      if (strlen($title) > 20) {
        $title = substr($title, 0, 17) . '...';
      } // if
      
      array_push($finalData, array(
        'title' => $title,
        'rate' => $rate,
        'link' => $link,
        'impressions' => $impressions[$post],
        'submissions' => $submissions[$post],
        'id' => $post
      ));
    } // foreach
    
    // Sort the decimal numbers
    usort($finalData, 'ois_sort_conversions');
    
    $finalData = array_slice($finalData, 0, $num_items_in_graph); 
    // Output conversion data to JSON
    ?>
      <script type="text/javascript">
        var conversionData = <?php
          echo json_encode($finalData);
        ?>;
      </script>
    <?php
    // Print the various display areas
    include dirname(__FILE__) . "/views/stats_display.html";
    //ois_print_top_ten_area();
    
    // Include any scripts needed for the graphs
    ois_include_d3_scripts();
    
    // Include the script for displaying the visualizations.
    $script_url = OIS_URL . "admin/topPosts/js/script.js";
    echo "<script type='text/javascript' src='$script_url'></script>";
    
    // Ouput the table of top ten posts
    ois_create_top_ten_post_display($finalData);
  } // else
}

function ois_create_top_ten_post_display ($data) {
?>
  <p>The following table contains the OptinSkin stats for each of your top ten posts</p>
  <table class="widefat ois_stats_table">
	<thead>
		<th><span class="glyphicon glyphicon-star"></span> Top 10 Posts</th>
		<th>Signups</th>
		<th>Impressions</th>
		<th>Conversion Rate (%)</th>
	</thead>
	<tbody>
	<?php
	foreach ($data as $row) {
  	echo "<tr><td><a href=\"{$row['link']}\">{$row['title']}</a>" .
  	  "<td>{$row['submissions']}</td>" .
  	  "<td>{$row['impressions']}</td>" .
  	  "<td>" . $row['rate'] * 100 . "</td></tr>";
	} // 
	?>
	</tbody>
</table>
  <?php
}


/**
 * ois_sort_conversions function.
 * 
 * Sorts decimal numbers.
 *
 * @post array is sorted, with largest rates in front
 * @access public
 * @param mixed $a
 * @param mixed $b
 * @return void
 */
function ois_sort_conversions($a, $b) {
    $result = 0;
    if ($a['rate'] < $b['rate']) {
        $result = 1;
    } else if ($a['rate'] > $b['rate']) {
        $result = -1;
    }
    return $result; 
} // ois_sort_conversions ()

?>