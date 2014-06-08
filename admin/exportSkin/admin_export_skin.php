<?php
function ois_export_skins()
{
	$all_skins = get_option('ois_skins');
	
	ois_section_title('Export Skins', 'Here you can export your skins to plain HTML and CSS', '');
	
	
	if (!isset($_GET['skin']))
	{
		echo "<h3>Select a skin to export</h3><ul>";
		foreach ($all_skins as $skin_id=>$skin)
		{
			$skin_title = $skin['title'];
			
			// Create a url to request the export of this skin.
			$uri = explode('?', $_SERVER['REQUEST_URI']);
			$export_url = $uri[0] . '?page=oisexport&skin=' . $skin_id;
			
			echo "<li><a href='$export_url'>$skin_title</a></li>";
			
		}
		echo "</ul>";
	} // if
	else
	{
		$skin_id = $_GET['skin'];
		//$skin_to_export = $all_skins[$skin_id];

		$skin_path = OIS_PATH . "/skins/$skin_id";
		$html_file = "$skin_path/static.html";
		$css_file = "$skin_path/style.css";

		$html_content = file_get_contents($html_file);
		$css_content = file_get_contents($css_file);
		// We need to add some things here to make this work

		$html_combined = "
<html>
	<head>
		<title>Skin $skin_id</title>
		<link href='./css/style.css' rel='stylesheet'>
	</head>
	<body>
		$html_content
	</body>
</html>";


		// Create a temporary directory with this data
		$temp_path = OIS_PATH . "admin/exportSkin/temp/skin-$skin_id";
		$temp_url = OIS_URL . "admin/exportSkin/temp/skin-$skin_id";
		$temp_html_path = "$temp_path/html";
		$temp_css_path = "$temp_path/css";

		$date = date("F j, Y, g:i a");
		$readme = "README \n\n";

		$readme .= "----------------------------------\nOptinSkin README for Exported Skin\n ----------------------------------\nGenerated on $date \n\nThe html content for your skin resides in the html directory, and the stylesheet in the css directory. An index.html file exists in the current directory, which you can use to check that your skin is displaying correctly. Also, you can use the index.html file to see how to embed your skin into a web page.";

		if ( !file_exists( $temp_html_path ) )
		{
			mkdir( $temp_html_path, 0777, true );
		} // if
		if ( !file_exists( $temp_css_path ) )
		{
			mkdir( $temp_css_path, 0777, true );
		} // if

		file_put_contents("$temp_html_path/static.html", stripslashes($html_content));
		file_put_contents("$temp_css_path/style.css", stripslashes($css_content));
		file_put_contents("$temp_path/index.html", $html_combined);

		$zip = new ZipArchive();
		$res = $zip->open("$temp_path.zip", ZipArchive::CREATE);
		if ($res === TRUE) {
			$zip->addFile("$temp_css_path/style.css", "css/style.css");
			$zip->addFile("$temp_html_path/static.html", "html/static.html");
			$zip->addFile("$temp_path/index.html", "index.html");
			$zip->addFromString( 'README.txt', $readme );
			echo "Added $zip->numFiles files to skin-$skin_id.zip. Your download should begin shortly.";
			$zip->close();
			
			//delete  temporary directories
			ois_recursive_rmdir($temp_path);
			
			// Redirect user to download
			?>
			<script type="text/javascript">
				location.href= "<?php echo "$temp_url.zip" ?>";
			</script>
			<?php

		} // if
		else
		{
			echo "Zip failed.";
		} // else

	}
}

?>