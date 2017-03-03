<h1>Settings</h1>
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
<div class="nav-tab-wrapper wp-ampify-tabs">
	<span class="nav-tab nav-tab-active" tab-id="wp-ampify-custom-css">Custom Css</span>
	<span class="nav-tab" tab-id="wp-ampify-fonts">Fonts</span>
</div>
<div class="tab-content">
	<div id="wp-ampify-fonts" style="display: none">
		<form action="" method="POST">
			<p>Add comma separated font links</p>
			<textarea name="wp_ampify_fonts" placeholder="Eg: https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" cols="100" rows="4"><?php echo implode(',',get_option('_wp_ampify_fonts', array())); ?></textarea>
			<?php wp_nonce_field('wp_ampify_fonts_nonce', 'wp_ampify_fonts_nonce'); ?>
			<br>
			<div>
				<button type="submit" class="button button-primary button-large">Save</button>
			</div>
		</form>
	</div>
	<div id="wp-ampify-custom-css">
		<style>
		pre.ace_editor {
		    width: 95%;
		    height: 500px;
		}
		</style>
		<p>Add your custom css here</p>
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
	</div>
</div>
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

	$('.wp-ampify-tabs span').click(function(){
		$(this).siblings().removeClass('nav-tab-active')
		$(this).addClass('nav-tab-active')
		var hash = $(this).attr('tab-id');
		$('#' + hash).siblings().hide()
		$('#' + hash).show()
	})
})
</script>