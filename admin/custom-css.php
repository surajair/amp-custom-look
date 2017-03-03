<h1>Custom CSS</h1>
<style>
pre.ace_editor {
    width: 95%;
    height: 500px;
}
</style>
<?php 

if(isset($_SESSION['wp_ampify_settings_saved'])){
	?>
	<div class="notice notice-success is-dismissible">
	    <p><?php _e( $_SESSION['wp_ampify_settings_saved'] ); ?></p>
	</div>
	<?php
	unset($_SESSION['wp_ampify_settings_saved']);
}
?>
<form action="" method="POST">
	<input type="hidden" name="wp_ampify_page" value="wp_ampify_custom_css"/>
	<input type="hidden" name="wp_ampify_action" value="update_custom_css"/>
	<?php wp_nonce_field('wp_ampify_css', 'wp_ampify_css'); ?>
	<textarea name="wp_ampify_custom_css" style="display: none"></textarea>
	<textarea id="wp-ampify-css-editor"><?php echo get_option('_wp_ampify_custom_css', ''); ?></textarea>
	<br>
	<div>
		<button type="submit" id="wp-ampify-css-editor-save" class="button button-primary button-large">Save Css</button>
	</div>
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/theme-chrome.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/mode-css.js"></script>
<script>
jQuery(document).ready(function($){
	document.getElementById('wp-ampify-css-editor').style.fontSize='15px';
	editor = ace.edit("wp-ampify-css-editor");
	editor.setTheme("ace/theme/chrome");
	var CssMode = ace.require("ace/mode/css").Mode;
	editor.session.setMode(new CssMode());
	$('#wp-ampify-css-editor-save').click(function(evt){
		evt.preventDefault();
		$('[name="wp_ampify_custom_css"]').val(editor.getValue());
		$(this).closest('form').submit()	
	})
})
</script>