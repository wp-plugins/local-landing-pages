<?php  
	global $wpdb;
	$tablename = $wpdb->prefix . 'llp';
	$settings = $wpdb->get_results("SELECT * FROM $tablename", ARRAY_A);
	$presets = array();
	$existing_settings = '';
	
	foreach($settings as $setting) {
		$language = $setting['language'];
		$presets[$language] = $setting['path'];
	}
	
	$countries = array("ar" => "Arabic","bn" => "Bengali","bg" => "Bulgarian","ca" => "Catalan","zh_CN" => "Chinese (Simplified)","zh_TW" => "Chinese (Traditional)","hr" => "Croatian","cs" => "Czech","da" => "Danish","nl" => "Dutch","en" => "English","en_GB" => "English (UK)","fil" => "Filipino","fi" => "Finnish","fr" => "French","de" => "German","el" => "Greek","gu" => "Gujarati","iw" => "Hebrew","hi" => "Hindi","hu" => "Hungarian","in" => "Indonesian","it" => "Italian","ja" => "Japanese","kn" => "Kannada","ko" => "Korean","lv" => "Latvian","lt" => "Lithuanian","ms" => "Malay","ml" => "Malayalam","mr" => "Marathi","no" => "Norwegian","or" => "Oriya","fa" => "Persian","pl" => "Polish","pt_BR" => "Portuguese (Brazil)","pt_PT" => "Portuguese (Portugal)","ro" => "Romanian","ru" => "Russian","sr" => "Serbian","sk" => "Slovak","sl" => "Slovenian","es" => "Spanish","sv" => "Swedish","ta" => "Tamil","te" => "Telugu","th" => "Thai","tr" => "Turkish","uk" => "Ukrainian","vi" => "Vietnamese");

// Look up existing settings and add them to a variable to print later.
	if(count($presets) > 0) {
		foreach($presets as $p_code => $p_path) {
			$existing_settings .= '<div class="countries"><select disabled><option value="' . $p_code . '" selected>' . $countries[$p_code] . '<option></select>&nbsp;<input type="text" readonly="readonly" class="enter-code" value="' . $p_path . '" /><a href="#" class="remove">-</a></div>';
		}
	}

	// Set up a brand new form.
		$new_form = '<div class="countries"><select name="language">';
		foreach($countries as $code => $country) {
			if(array_key_exists($code,$presets)) {
			$new_form .= '<option value="' . $code . '" disabled="disabled">' . $country . '</option>';
			} else {
			$new_form .= '<option value="' . $code . '">' . $country . '</option>';			
			}
		}
		$new_form .= '</select>&nbsp;<input type="text" name="path" class="enter-code" /></div>';

?>

<div class="wrap">
<h2>Local Landing Pages</h2>
<p class="instructions">Use the select menu to choose the country you want to set a landing page for, then type the relative path to the landing page you would like users to see. For example, if your German landing page is at http://mysite.com/pages/germany, use 'pages/germany'.</p>
<form name="country_select" id="country_select">  
	<div id="drop-downs">
		
		<?php
			echo $existing_settings;
			echo $new_form;
		?>
		
	</div>
	<p class="submit">  
		<input type="submit" id="submit" name="Submit" value="<?php _e('Update Options', 'llp_trdom' ) ?>" />  
	</p>
</form>
</div>

<script type="text/javascript">

	var added = jQuery.makeArray();
	var codes = jQuery.makeArray();
	
	jQuery(document).ready(function() {
		jQuery(".countries").children('select:last').children('option').each(function(index) {
			codes.push(jQuery(this).val());
		});
	});
	
	// Append more options to the form.
	jQuery('.add-more').live('click',function(){
		count++;
		jQuery("#drop-downs").append('<?php echo $new_form; ?>');
		var newForm = jQuery(".countries:last");
		var select = newForm.children(":input:first");
		var text = newForm.children(":input:last");
		return false;
	});
	
	jQuery('.remove').live('click',function(){
		var selected = jQuery(this).siblings('select').children('option:selected').val();
		var option = jQuery(this).siblings('select').children('option:selected');
		var path = jQuery(this).siblings('input:text').val();
		var newForm = jQuery(".countries").children('select:last');	
		var disabled = newForm.children('option[value="' + option.val() + '"]');
		disabled.removeAttr('disabled');
				var data = {
						action: 'remove',
						language: selected
					};
					jQuery.post(ajaxurl, data, function(response) {
						//alert('Got this from the server: ' + response);
					});						
			jQuery(this).parent('.countries').remove();				
		return false;
	});
	
	jQuery('#submit').click(function(e){
		e.preventDefault();
		if(jQuery('.enter-code:last').val() != '') {
			var data = {
					action: 'add',
					data: jQuery('#country_select').serialize()
				};
			jQuery.post(ajaxurl, data, function(response) {
				//alert('Got this from the server: ' + response);
			});
			var input = jQuery(".countries").children('input:last');
			input.attr('readonly','readonly');
			var select = jQuery(".countries").children('select:last');
			var add = select.children('option:selected').val();
			added.push(add);
			select.attr('disabled', 'disabled');
			select.removeAttr('name');
			input.after('<a href="#" class="remove">-</a>');
			input.removeAttr('name');
			jQuery("#drop-downs").append('<?php print $new_form; ?>');
			var newForm = jQuery(".countries").children('select:last');
			for(var i in added) {
				newForm.children("option[value='"+ added[i] +"']").attr('disabled', 'disabled');								
			}
//			console.log(newForm.children('option:not(:disabled):first'));
			newForm.children('option:not(:disabled):first').attr('selected','selected');
		}
	});
	
</script>

<style type="text/css">
	.countries {
		padding-bottom: 1em;
	}
	
	select {
		position: absolute;
		text-align: left;
		width: 14em;
	}
	.enter-code {
		margin-left: 15em;
		width: 10em;
	}

	.add-more, .remove {
		margin-left: 1em;
		position: relative;
	}
	
</style>