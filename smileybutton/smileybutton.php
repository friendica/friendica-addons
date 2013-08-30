<?php
/**
 * Name: Smileybutton
 * Description: Adds an smileybutton to the Inputbox
 * Version: 0.1
 * Author: Johannes Schwab <johannes_schwab@gmx.de>
 */


function smileybutton_install() {

	/**
	 * 
	 * Register hooks for jot_tool and plugin_settings
	 *
	 */

	register_hook('jot_tool', 'addon/smileybutton/smileybutton.php', 'show_button');
	register_hook('plugin_settings', 'addon/smileybutton/smileybutton.php', 'smileybutton_settings');
	register_hook('plugin_settings_post', 'addon/smileybutton/smileybutton.php', 'smileybutton_settings_post');
 
	logger("installed smileybutton");
}


function smileybutton_uninstall() {

	/**
	 *
	 * Delet registered hooks
	 *
	 */

	unregister_hook('jot_tool',    'addon/smileybutton/smileybutton.php', 'show_button');	
	unregister_hook('plugin_settings', 'addon/smileybutton/smileybutton.php', 'smileybutton_settings');
	unregister_hook('plugin_settings_post', 'addon/smileybutton/smileybutton.php', 'smileybutton_settings_post');
	 
	logger("removed smileybutton");
}



function show_button($a, &$b) {

	/**
	 *
	 * Check if it is a local user and he has enabled smileybutton
	 *
	 */

	if(! local_user())
		return;

	$active = get_pconfig(local_user(), 'smileybutton', 'enable');

	if(! $active)
		return;

	/**
	 *
	 * Prepare the Smilie-Arrays
	 *
	 */

	/**
	 *
 	 * I have copied this from /include/text.php and removed dobles
	 *
	 */

	$texts =  array( 
		'&lt;3', 
		'&lt;/3', 
		':-)', 
		';-)', 
		':-(', 
		':-P', 
		':-X', 
		':-D', 
		':-O', 
		'\\\\o/', 
		'O_o', 
		":\'(", 
		":-!", 
		":-/", 
		":-[", 
		"8-)",
		':beer', 
		':coffee', 
		':facepalm',
		':like',
		':dislike',
                '~friendica',
                'red#'

	);

	$icons = array(
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-heart.gif" alt="<3" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-brokenheart.gif" alt="</3" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-smile.gif" alt=":-)" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-wink.gif" alt=";-)" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-frown.gif" alt=":-(" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-tongue-out.gif" alt=":-P" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-kiss.gif" alt=":-X" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-laughing.gif" alt=":-D" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-surprised.gif" alt=":-O" />',                
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-thumbsup.gif" alt="\\o/" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-Oo.gif" alt="O_o" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-cry.gif" alt=":\'(" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-foot-in-mouth.gif" alt=":-!" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-undecided.gif" alt=":-/" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-embarassed.gif" alt=":-[" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-cool.gif" alt="8-)" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/beer_mug.gif" alt=":beer" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/coffee.gif" alt=":coffee" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/smiley-facepalm.gif" alt=":facepalm" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/like.gif" alt=":like" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/dislike.gif" alt=":dislike" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/friendica-16.png" alt="~friendica" />',
		'<img class="smiley" src="' . $a->get_baseurl() . '/images/rhash-16.png" alt="red" />'
	);
	
	/**
	 * 
	 * Call hooks to get aditional smileies from other addons
	 *
	 */

	$params = array('texts' => $texts, 'icons' => $icons, 'string' => ""); //changed
	call_hooks('smiley', $params);


	/**
	 *
	 * Generate html for smileylist
	 *
	 */

	$s = "\t";
	for($x = 0; $x < count($params['texts']); $x ++) {
		$icon = $params['icons'][$x];
		$icon = str_replace('/>', 'onclick="smileybutton_addsmiley(\'' . $params['texts'][$x] . '\')"/>', $icon);
		$s .= $icon . ' ';
		if ($x != 0 && $x % 10 == 0) {
			$s .= "<br />\n\t";
		}
	}

	/**
	 *
	 * Add the button to the Inputbox
	 *
	 */	

	$b = "<div id=\"profile-smiley-wrapper\" style=\"display: block;\" >\n";
	$b .= "\t<img src=\"" . $a->get_baseurl() . "/addon/smileybutton/icon.gif\" onclick=\"toggle_smileybutton()\" alt=\"smiley\">\n";
	$b .= "\t</div>\n";
 
	/**
	 *
	 * Write the smileies to an hidden div
	 *
	 */

	$b .= "\t<div id=\"smileybutton\" style=\"display:none;\">\n";
	$b .= $s . "\n"; 
	$b .= "</div>\n";

	/**
	 *
	 * Function to show and hide the smiley-list in the hidden div
	 *
	 */

	$b .= "<script>\n"; 
	$b .= "	smileybutton_show = 0;\n";
	$b .= "	function toggle_smileybutton() {\n";
	$b .= "	if (! smileybutton_show) {\n";
	$b .= "		$(\"#smileybutton\").show();\n";
	$b .= "		smileybutton_show = 1;\n";
	$b .= "	} else {\n";
	$b .= "		$(\"#smileybutton\").hide();\n";
	$b .= "		smileybutton_show = 0;\n";
	$b .= "	}}\n";

	/**
	 *
	 * Function to add the chosen smiley to the inputbox
	 *
	 */

	$b .= "	function smileybutton_addsmiley(text) {\n";
	$b .= "		v = $(\"#profile-jot-text\").val()\n";
	$b .= "		v = v + text\n";
	$b .= "		$(\"#profile-jot-text\").val(v)\n";
	$b .= "	}\n";
	$b .= "</script>\n";
}





/**
 *
 * Set the configuration
 *
 */

function smileybutton_settings_post($a,$post) {
	if(! local_user())
		return;
	if($_POST['smileybutton-submit'])
		set_pconfig(local_user(),'smileybutton','enable',intval($_POST['smileybutton']));
}


/**
 *
 * Add configuration-dialog to form
 *
 */


function smileybutton_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/smileybutton/smileybutton.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = get_pconfig(local_user(),'smileybutton','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>Smileybutton settings</h3>';
	$s .= '<div id="smileybutton-enable-wrapper">';
	$s .= '<label id="smileybutton-enable-label" for="smileybutton-checkbox">Enable Smileybutton Plugin</label>';
	$s .= '<input id="smileybutton-checkbox" type="checkbox" name="smileybutton" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="smileybutton-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}
