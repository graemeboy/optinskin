<h2 class="nav-tab-wrapper">
	<a href="<?php echo $urls['current']; ?>" class="nav-tab nav-tab-active"><span class="glyphicon glyphicon-stats"></span> Skin Performance</a>
	<a href="<?php echo $urls['edit']; ?>" class="nav-tab"><span class="glyphicon glyphicon-edit"></span> Edit Skin</a>
	<a href="<?php echo $urls['duplicate']; ?>" class="nav-tab"><span class="glyphicon glyphicon-plus"></span> Duplicate Skin</a>
	<a href="<?php echo $urls['export']; ?>" class="nav-tab"><span class="glyphicon glyphicon-export"></span> Export Skin as HTML</a>
	<a class="nav-tab" href="<?php echo $urls['trash'] ?>"><span class="glyphicon glyphicon-trash"></span> Delete Skin</a>
	<a class="nav-tab" href="<?php echo $urls['clear-stats'] ?>"><span class="glyphicon glyphicon-minus"></span> Clear Skin's Stats</a>
</h2> <!-- .nav-tab-wrapper -->

<div id="ois-custom-info-wrap">
	<h3 style="margin:5px 0;">How to embed this skin in a custom position</h3>
	<div style="line-height:30px;">
		To use this skin as a shortcode, simply put
		<span class="ois_code_snippet" id="ois_use_shortcode">[ois skin="<?php echo $skin_id ?>"]</span> into any of your posts.<br/>To use it on a php page, such as <em>header.php</em> or <em>footer.php</em>, use the php code
		<span class="ois_code_snippet" id="ois_do_shortcode">&lt;?php echo do_shortcode( '[ois skin="<?php echo $skin_id; ?>"]' ); ?&gt;</span><br/>
		If you want to split-test using the shortcode, add other skin ID inside of split="". For example: 
		<span class="ois_code_snippet" id="ois_other_shortcode">[ois skin="<?php echo $skin_id; ?>" split=""]</span>
		, or <span class="ois_code_snippet" id="ois_other2_shortcode">[ois skin="<?php echo $skin_id; ?>" split="7,8"]</span>
	</div>
</div> <!-- custom-info-wrap -->