<?php
//=========================================================================
//! admin_custom.php
//  Contains the functions needed for creating and editing custom designs.
//=========================================================================

/**
 * ois_custom function.
 * Sets up the page for adding and editing custom designs.
 * 
 * @access public
 * @return void
 */
function ois_custom() {
	
	// Custom Designs will hold an array of design ids
	$custom_designs = get_option('ois_custom_designs');
	
	// Output a suitable header with title for the page.
	ois_section_title('Create a Custom Design', 'This tool is for creating custom designs for your skins.', 'Enter  your HTML into the editor below, and preview your design as it instantly updates. Once you save, it will be available for you when you add new skins.');
	
	// Check if we're updating a design, or adding one.
	ois_save_custom_design($custom_designs);
	
	if (isset($_GET['id'])) {
		// Editing a design

		$design_id = trim($_GET['id']);
		$custom_path = OIS_PATH . "customDesigns/$design_id";

		if (file_exists($custom_path))
		{
			$this_html = file_get_contents("$custom_path/static.html");
			$this_css = file_get_contents("$custom_path/style.css");
		} // if
	} // if
		
	// Create navigation to existing designs.
	ois_custom_designs_header($custom_designs);
?>

	<link href="<?php echo OIS_URL ?>admin/css/glyphicons.bootstrap.min.css" rel="stylesheet" />
	<link href="<?php echo OIS_URL ?>admin/customDesign/css/style.css" rel="stylesheet">

	<table class="widefat">
	<thead>
		<th>Preview of Your Design</th>
	</thead>
	<tbody>
		<tr class="alternate">
			<td>
				<style id="ois_custom_css_update_area">
					<?php
	if (isset($this_css) && trim($this_css) != '') {
		echo $this_css;
	}
?>
				</style>
				<div id="ois_custom_update_area">
					<?php
	if (isset($this_html) && trim($this_html) != '') {
		echo $this_html;
	} else {
		echo '<p style="padding-top: 20px;" class="ois_custom_waiting">Type into the Text Area Below...</p>';
	}
?>
				</div>
			</td>
		</tr>
	</tbody>
	</table>

	<form method="post">
	<table class="widefat">
	<thead>
		<th>Code Editor</th>
	</thead>
	<tbody>

	<tr>

	<td>
	<div>
	<h3>HTML Code</h3>
	<textarea id="ois_custom_editor" name="design_html"><?php
	if (isset($this_html) && trim($this_html) != '') {
		echo $this_html;
	} else {
		echo 'Add your custom HTML here...';
	}
	?></textarea>
	</div>
	<div>
	<h3>CSS Code</h3>
	<textarea id="ois_custom_css_editor" name="design_css"><?php
	if (isset($this_css) && trim($this_css) != '') {
		echo $this_css;
	} else {
		echo 'CSS goes here...';
	}
	?></textarea>
	</div>

	<script type="text/javascript">

	jQuery(document).ready(function ($) {
		$('#ois_custom_editor').focus(function () {
			if ($(this).val() == 'Add your custom HTML here...') {
				$(this).val('');
			}
		});
		$('#ois_custom_css_editor').focus(function () {
			if ($(this).val() == 'CSS goes here...') {
				$(this).val('');
			}
		});

		$('#ois_custom_editor, #ois_custom_css_editor').keydown(function (event)
		{

			if (event.keyCode == 9)
			{
				var tab = "    ";
				var t = event.target;
			    var ss = t.selectionStart;
			    var se = t.selectionEnd;
				event.preventDefault();

		        if (ss != se && t.value.slice(ss,se).indexOf("n") != -1) {
		            var pre = t.value.slice(0,ss);
		            var sel = t.value.slice(ss,se).replace(/n/g,"n"+tab);
		            var post = t.value.slice(se,t.value.length);
		            t.value = pre.concat(tab).concat(sel).concat(post);

		            t.selectionStart = ss + tab.length;
		            t.selectionEnd = se + tab.length;
				}
				else
				{
		            t.value = t.value.slice(0,ss).concat(tab).concat(t.value.slice(ss,t.value.length));
		            if (ss == se)
		            {
		                t.selectionStart = t.selectionEnd = ss + tab.length;
		            } // if
		            else
		            {
		                t.selectionStart = ss + tab.length;
		                t.selectionEnd = se + tab.length;
		            } // else
		        } // else
			} // if
		}); // keydown

		$('#ois_custom_editor').keyup(function (event) {
			$('#ois_custom_update_area').html($('#ois_custom_editor').val());
		}); // keyup
		$('#ois_custom_css_editor').keyup(function (event) {
			$('#ois_custom_css_update_area').html($('#ois_custom_css_editor').val());
		}); // keyup
	}); // document.ready
	</script>

	<?php
	if (isset($this_html) && trim($this_html) != '')
	{
		// Necessarily editing a design
?>
		<input type="hidden" name="design_id" value="<?php echo $design_id; ?>" />
			<?php
	}
?>
	<p><input type="submit" class="button-primary" value="Save Design" /></p>
	<?php
	wp_nonce_field('ois_custom', 'custom_design');
?>
	</td></tr>
	</tbody>
	</table>
	</form>


	<div id="ois-instructions">How to use the HTML/CSS Editor</div>
	<table class="widefat">
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 1. Go to Custom Design Editor</div>
				<div>The editor provides a basic way to create your own designs, and still have OptinSkin power your stats and split-testing.</div>
			</td>

			<td>
				<img class="ois_custom_screen" src="<?php echo OIS_URL . 'admin/customDesign/img/custom_start.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 2. Enter HTML Code</div>
				 <div>All you need to do is write some HTML code (or copy and paste it from your service provider) into the first box on the page. Notice how the preview updates as you do so.</div>
			</td>
			<td>
				<img class="ois_custom_screen" src="<?php echo OIS_URL . 'admin/customDesign/img/custom_html.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 3. Create CSS Code</div>
				<div>You can also write some CSS in the box below, which will help you to style your design the way that you want it.</div>
			</td>
			<td>
				<img class="ois_custom_screen" src="<?php echo OIS_URL . 'admin/customDesign/img/custom_css.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="ois_custom_info_sec">
				<div class="ois_custom_title">Step 4. View Final Product and Save</div>
				<div>Once you save a design, you can go over to the <em>Add Skin</em> page, and find your custom design by clicking through the slider in the <em>Customize Your Design</em> section. Once you've chosen this design, go ahead and create your skin wherever you want, and your design will appear in your posts.</div>
			</td>
			<td>
				<img class="ois_custom_screen" src="<?php echo OIS_URL . 'admin/customDesign/img/custom_prefinal.png'; ?>" />
			</td>
		</tr>
	</table>
	<div>


	</div>


	<style type="text/css">
		.ois_custom_screen {
			padding: 2px;
			border: 1px solid #ccc;
			max-height: 450px;
			max-width: 660px;
		}
		.ois_custom_info_sec {
			min-width:200px;
			width:34% !important;
			padding:5px 5px 15px 5px;
		}
		.ois_custom_title {
			font-size: 14px;
			font-weight: bold;
			padding: 10px 0 5px 0;
		}
	</style>
	<?php

	ois_section_end();

} // ois_custom



/**
 * ois_custom_designs_header function.
 * Outputs the header for the Custom Designs page.
 *
 * @access public
 * @param mixed $custom_designs
 * @return void
 */
function ois_custom_designs_header($custom_designs)
{
	// Check if we are currently editing an existing design.
	if (isset($_GET['id']))
	{
		$editing_design = $_GET['id'];
	} // if
	else
	{
		$editing_design = 0;
	} // else
	?>
	<h2 class="nav-tab-wrapper">
	<?php 
	// Get the base URI for editing and creating designs.
	$uri = explode('?', $_SERVER['REQUEST_URI']);
	// Add a link to creating a new design
	$create_url = $uri[0] . '?page=create-design';
	// Output the link to the navigation bar.
	?>
	<a href="<?php echo $create_url; ?>" class="nav-tab <?php if (!$editing_design) echo "nav-tab-active" ?>">
		<span class="glyphicon glyphicon-file"></span> 
		New Design
	</a>
	<?php
	// Check if custom designs have been created.
	if (!empty($custom_designs))
	{
		// If so, create a link to edit each one of those designs.
		
		
		foreach ($custom_designs as $design_id)
		{
			// Create a custom editing url.
			$custom_url = $create_url . '&id=' . $design_id;
			// Output the link to the navigation bar.
			?>
			<a href="<?php echo $custom_url; ?>" class="nav-tab <?php 
					if ($editing_design == $design_id) echo "nav-tab-active" 
				?>">
				<span class="glyphicon glyphicon-edit"></span> 
				Edit Design <?php echo $design_id ?>
			</a>
			<?php
		} // foreach
	} // if
	// Add a link to instructions
	?>
		<a href="#ois-instructions" class="nav-tab">
			<span class="glyphicon glyphicon-info-sign"></span> Instructions
		</a>
	</h2> <!-- .nav-tab-wrapper -->
<?php
} // ois_save_custom_design()

/**
 * ois_save_design function.
 * Saves the design, if one is posted, new or edited.
 * 
 * @access public
 * @param mixed $custom_designs
 * @return void
 */
function ois_save_custom_design($custom_designs)
{
	if (!empty($_POST) && check_admin_referer('ois_custom', 'custom_design') ) {
		// We need to save this to a file.


		if (!isset($_POST['design_id']))
		{
			// We are creating a new design id
			$design_id = 1;
			if (!empty($custom_designs))
			{
				while (in_array($design_id, $custom_designs))
				{
					$design_id++;
				} // while
				array_push($custom_designs, $design_id);
			} // if
			else
			{
				$custom_designs = array(1);
			}
			// We should now have a design id that isn't in use.
			
			update_option('ois_custom_designs', $custom_designs);
				
		} // if
		else
		{
			$design_id = $_POST['design_id'];
		} // else

		$custom_path = OIS_PATH . "customDesigns/$design_id";

		if ( !file_exists( $custom_path ) )
		{
			mkdir( $custom_path, 0777, true );
		} // if

		if (isset($_POST['design_html']))
		{
			$html_content = $_POST['design_html'];
			file_put_contents("$custom_path/static.html", stripslashes($html_content));
		} // if

		if (isset($_POST['design_css']))
		{
			$css_content = $_POST['design_css'];
			file_put_contents("$custom_path/style.css", stripslashes($css_content));
		} // if

	} // if
} // ois_save_design ()

?>