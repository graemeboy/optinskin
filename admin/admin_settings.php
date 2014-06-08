<?php


/**
 * ois_general_settings function.
 * Sets up the options page for OptinSkin Settings.
 *
 * @access public
 * @return void
 */
function ois_general_settings() {

	if ( !empty($_POST) ) {
		if ( !check_admin_referer('ois_general_field', 'save_data')) {
			echo 'Sorry, your nonce did not verify.';
			exit;
		} // if
		else {
			if (!empty($_POST['stats_impressions_disable']) && $_POST['stats_impressions_disable']) {
				$stats_impressions_disable = 'yes';
			} // if
			else {
				$stats_impressions_disable = 'no';
			} // else
			if (!empty($_POST['stats_submissions_disable']) && $_POST['stats_submissions_disable'] == 'yes') {
				$stats_submissions_disable = 'yes';
			} // if
			else {
				$stats_submissions_disable = 'no';
			} // else
			update_option('stats_impressions_disable', $stats_impressions_disable);
			update_option('stats_submissions_disable', $stats_submissions_disable);
			ois_notification('Your General Settings Have Been Updated!', '', '');
		} // else
	} // if

	ois_section_title('General Settings', 'Here you can update your general settings', '');
	ois_start_option_table('Configure Stats', 'no', '');
	$stats_impressions_disable = get_option('stats_impressions_disable');
	$stats_submissions_disable = get_option('stats_submissions_disable');
?>
	<tr>
		<th scope="row" style="width:280px;">
			Disable Impression Statistics <br/>
		</th>
		<td>
			<p>
				<input	type="checkbox"
						name="stats_impressions_disable"
						value="yes"
				<?php if ($stats_impressions_disable == 'yes') { echo 'checked="checked"'; } ?> /> Disable
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			Disable Submission Statistics <br/>
		</th>
		<td>
			<p>
				<input	type="checkbox"
						name="stats_submissions_disable"
						value="yes"
				<?php if ($stats_submissions_disable == 'yes') { echo 'checked="checked"'; } ?> /> Disable
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			Installed Extensions <br/>
		</th>
		<td>
		  <?php
		    $extensions = get_option('ois_extensions');
		    if (empty($extensions)) {
		      echo "You have no extensions installed";
		    } else {
  		    foreach ($extensions as $name=>$url) {
    		    echo "<div>$name</div>";
  		    } // foreach
		    } // else
		  ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			Save Options
		</th>
		<td>
			<?php wp_nonce_field('ois_general_field', 'save_data'); ?>
			<input type="submit" class="ois_super_button" value="Save Options" />
		</td>
	</tr>
	</table>
	</form>
<?php
	ois_section_end();
} // ois_general_settings()
?>