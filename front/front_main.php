<?php

add_action('init', 'ois_front_setup');


/**
 * ois_front_setup function.
 * 
 * @access public
 * @return void
 */
function ois_front_setup()
{
	// Load the necessary scripts.
	add_action( 'wp_enqueue_scripts', 'ois_load_scripts' );
	add_action( 'wp_enqueue_scripts', 'ois_load_styles' );
	add_filter( 'the_content', 'ois_content_skin' );
} // ois_front_setup

/**
 * ois_get_optins_for_content function.
 * 
 * @access public
 * @param mixed $all_skins
 * @return void
 */
function ois_get_optins_for_content($all_skins)
{
	$optins_for_content = array();

	if (is_single() && !empty($all_skins))
		{ // check if page is a post
		foreach ( $all_skins as $skin_id => $skin )
		{
			// find out if we should display this skin on this page
			if (!empty($skin['exclude_categories'])) {
				$exclude_categories = explode(',', $skin['exclude_categories']);
			}
			if (!empty($skin['exclude_posts']))
			{
				$exclude_posts = explode(',', $skin['exclude_posts']);
			}
			if (empty($exclude_categories) || !in_category($exclude_categories) &&
				empty($exclude_posts) || !is_single($exclude_posts))
			{
				// we need to do something with this skin.
				if (!empty($skin['post_position']))
				{
					$position = $skin['post_position'];
				}
				
				if ($position != 'custom')
				{
					if (!empty($optins_for_content[$position]))
					{
						array_push($optins_for_content[$position], $skin);
					} // if
					else
					{
						$optins_for_content[$position] = array($skin);
					} // else
				} // if not custom
			} // if
		} // foreach
	} // if not empty and disabled
	return $optins_for_content;
}

/**
 * ois_get_optins_to_go function.
 *
 * Returns final skins (which prunes some of $optins_for_content for split-testing)
 *
 * @access public
 * @param mixed $optins_for_content
 * @return void
 */
function ois_get_optins_to_go($optins_for_content)
{
	$skins_to_go = array(); // because there might be split-testing.
	if (!empty($optins_for_content))
	{
		foreach($optins_for_content as $position=>$skins)
		{
			if (count($skins) > 1)
			{
				// May have opportunity for split-testing here.
				$split_testers = array();

				foreach ($skins as $skin)
				{
					if (isset($skin['split_testing']) &&
						$skin['split_testing'] == 'yes')
					{
						array_push($split_testers, $skin);
					} // if
					else
					{
						array_push($skins_to_go, $skin);
					} // else
				} // foreach

				// We need to choose one of these skins if split testing -
				// pick one from split_testers and put in skins_to_go.
				if (count($split_testers) > 0)
				{
					$rand_key = array_rand($split_testers, 1);
					array_push($skins_to_go, $split_testers[$rand_key]); // random selection
				} // if
			} // if
			else
			{
				array_push($skins_to_go, $skins[0]);
			} // else
		} // foreach
	} // if
	return $skins_to_go;
}

/**
 * ois_content_skin function.
 * 
 * @access public
 * @param mixed $content
 * @return void
 */
function ois_content_skin($content) {

	if (!is_feed() &&  get_post_type() == 'post')
	{
		$all_skins = get_option( 'ois_skins' );

		$optins_for_content = ois_get_optins_for_content($all_skins);
		// $optins_for_content is now an array of skin matched to position

		$skins_to_go = ois_get_optins_to_go($optins_for_content);
		// $skins_to_go are the final skins that will appear in the content

		if (!empty($skins_to_go))
		{
			// we're finally going to serve the skin to the page.
			foreach ($skins_to_go as $skin) {
			
			
				// find out design
				$position = $skin['post_position'];
				$skin_id = $skin['id'];

				if ($position == 'post_bottom')
				{
					$content .= '<div style="clear:both;"></div>';
					$content .= ois_make_skin($skin_id);
				} // if bottom
				else if ($position == 'post_top')
					{
						$content .= '<div style="clear:both;"></div>';
						$content = ois_make_skin($skin_id) . $content;
					} // else if top
				else if ($position == 'below_first')
					{
						// now we have the content from second paragraph.
						$paragraphs = explode('</p>', $content);
						$content = '';
						for ($i = 0; $i < count($paragraphs); $i++) {
							$content .= $paragraphs[$i] . '</p>';
							if ($i == 0)
							{
								$content .= '<div style="margin-bottom:10px;clear:both;">' .
									ois_make_skin($skin_id) . '</div>';
							} // if
						} // for
					} // if below first paragraph
				else if ($position == 'below_x_paragraphs')
					{
						// Below a custom number of paragraphs.
						$paragraphs = explode('</p>', $content);
						$old_content = $content;
						$content = '';

						if (!empty($skin['below_x_paragraphs']) && $skin['below_x_paragraphs'] > 0)
						{
							$x = $skin['below_x_paragraphs'];
						} // if
						else
						{
							$x = 0;
						} // else

						// Compare x to the number that actually exists
						if ($x == 0)
						{
							// well...x has been set up user to be zero, so the skin should come first.
							$content = '<div style="margin-bottom:10px;clear:both;">' .
								ois_make_skin($skin_id) . '</div>' . $old_content;
						} // if
						else if ($x > count($paragraphs))
								{ // Not enough paragraphs, put skin at end of post.
								$content = $old_content . '<div style="margin-bottom:10px;clear:both;">'.
									ois_make_skin($skin_id) . '</div>';
							} // else if
						else
						{
							// good, proceed to put skin after x paragraphs.
							for ($i = 1; $i <= count($paragraphs); $i++)
							{
								$content .= $paragraphs[$i - 1] . '</p>';
								if ($i == $x)
								{
									$content .= '<div style="margin-bottom:10px;clear:both;">' .
										ois_make_skin($skin_id) . '</div>';
								} // if
							} // for
						} // else
					} // else if below x
				else if ($position == 'floated_second')
					{
						$paragraphs = explode('<p>', $content);
						$content = '';
						$target_par = 2;
						for ($i = 1; $i < count($paragraphs); $i++)
						{
							if (trim($paragraphs[$i - 1]) != '')
							{
								$content .= '<p>';
								if ($i == $target_par)
								{
									$content .= '<div style="float:right">';
									$content .= ois_make_skin($skin_id);
									$content .= '</div>';
								} // if

								$content .= $paragraphs[$i];
							} // if
							else
							{
								$target_par++;
							} // else
						} // for
					} // else if float right of second
			} // foreach
		} // if !empty skins to go
	} // if not is feed and is single post
	return $content;
} // ois_content_skin


/**
 * ois_load_scripts function.
 * 
 * Enqueues the relevent scripts 
 *
 * @access public
 * @return void
 */
function ois_load_scripts() {
	
	global $wp_query;
	// The optin.js script
	$script_url = OIS_EXT_URL . "min/optin1.min.js";
	// Depends on jQuery
	wp_register_script( 'ois_optin', $script_url, array('jquery') );
	wp_enqueue_script( 'ois_optin' );

	// Localize data
	// Create an array with the basic data for localization.
	$stats_submissions_disable = get_option('stats_submissions_disable');
	if (empty($stats_submissions_disable) || $stats_submissions_disable != 'yes')
	{
		$stats_submissions_disable = 'no';
	}
	$ois_data = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ois_submission_nonce' => wp_create_nonce ('ois-submit-nonce'),
		'disable_submissions_stats' => $stats_submissions_disable,
		'postID' => $wp_query->post->ID
	);
	// Localize data for this script.
	wp_localize_script( 'ois_optin', 'ois', $ois_data );
}


/**
 * ois_load_styles function.
 * 
 * Enqueues the necessary styles, including normalize.
 *
 * @access public
 * @return void
 */
function ois_load_styles() {
	$all_skins = get_option( 'ois_skins' );
	if (!empty($all_skins)) {
		// Enqueue the OptinSkin normalization stylesheet
		$style_url = OIS_EXT_URL . 'min/normalize.min.css';
		wp_register_style( 'ois_normalize', $style_url );
		wp_enqueue_style( 'ois_normalize' );

		// Enqueue the CSS for each of the skins
		foreach ( $all_skins as $skin_id => $skin )
		{
			$css_dir = OIS_PATH . "skins/$skin_id/style.css";
			$css_url = OIS_URL . "skins/$skin_id/style.css";

			if (file_exists($css_dir))
			{
				wp_register_style( "ois-$skin_id", $css_url );
				wp_enqueue_style( "ois-$skin_id" );
			} // if
		} // foreach
	} // if not empty
} // ois_load_styles()

?>