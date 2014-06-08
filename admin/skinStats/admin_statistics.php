<?php
function ois_statistics() {

  // We have d3.js here, so include the necessary files.
  ois_include_d3_scripts();
  
	ois_section_title('Compare Skin Performance', 'Which which skins convert the best', '');
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'optinskin';
	$sql = "SELECT * FROM $table_name " .
	// The data must come only from the last 30 days
  "WHERE (ts > DATE_SUB(now(), INTERVAL 30 DAY));";
	$rows = $wpdb->get_results($sql);
	$all_stats = array();
	if (!empty($rows)) {
  	foreach ($rows as $row) {
  		$new_stat = array(
  			's' => $row->skin,
  			'm' => $row->submission,
  		);
  		array_push($all_stats, $new_stat);
  	} // foreach
	} // if
	$skins = get_option('ois_skins');
	$stats_range = 10;
	$uri = explode('?', $_SERVER['REQUEST_URI']);
	$page_url = $uri[0] . '?page=ois-';
	
	$title_and_rate = array();
?>
	<table class="widefat">
		<thead>
			<th>Skin Name</th>
			<th>Impressions</th>
			<th>Submits</th>
			<th>Conversion Rate</th>
		</thead>
	<?php
	if (!empty($all_stats)) {
		foreach ($skins as $skin) {
		  $skin_title = $skin['title'];
				echo '<tr>';
				echo '<th><a href="' . $page_url . $skin['id'] . '" >' . $skin_title . '</a></th>';
				$impressions = array();
				$submits = array();
				
				foreach ($all_stats as $stats) {
					if (!empty($stats['s'])) {
						if ($stats['s'] == $skin['id']) {
							if (!empty($stats['m'])) {
								array_push($submits, $stats);
							} else {
								array_push($impressions, $stats);
							}
						}
					}
				}
				$num_imp = count($impressions);
				$num_sub = count($submits);
				echo '<td>' . $num_imp . '</td>';
				echo '<td>' . $num_sub . '</td>';
				if (count($impressions) != 0) {
				  $rate = round(100 * $num_sub/$num_imp, 2);
					echo '<td>' . $rate . '%</td>';
				} else {
				  $rate = 0;
					echo '<td>Unknown</td>';
				} // else
				if ($rate > 0) {
  				array_push($title_and_rate, array ( 'title' => $skin_title, 'rate' => $rate ));
				} // if
				echo '</tr>';
		} // foreach
	} // if
?>
	</table>
	
	<h3>Top Performing Skins by Conversion Rate</h3>
	<div id="skinsPie"></div>
	
	<script type="text/javascript">
        var w = 360, //width
            h = 380, //height
            r = 160, //radius
            //colors = d3.scale.category20c();
        color = d3.scale.category20c();

        honorsData = <?php echo json_encode($title_and_rate) ?>;
        var vis = d3.select("#skinsPie")
            .append("svg:svg")
            .data([honorsData])
            .attr("width", w)
            .attr("height", h)
            .append("svg:g")
            .attr("transform", "translate(" + r + "," + (r + 10) + ")")
         var arc = d3.svg.arc()
            .outerRadius(r);
        var pie = d3.layout.pie()
            .value(function(d) {
                return d.rate;
            });
        var arcs = vis.selectAll("g.slice")
            .data(pie)
            .enter()
            .append("svg:g")
            .attr("class", "slice");
        arcs.append("svg:path")
            .attr("fill", function(d, i) {
                return color(i);
            })
            .attr("d", arc);
        arcs.append("svg:text")
            .attr("transform", function(d) {
                d.innerRadius = 0;
                d.outerRadius = r;
                return "translate(" + arc.centroid(d) + ")";
            })
            .attr("text-anchor", "middle")
            .attr("fill", "#fff")
            .text(function(d, i) {
                return honorsData[i].title;
            });
    </script>
	<?php
	ois_section_end();
}
?>