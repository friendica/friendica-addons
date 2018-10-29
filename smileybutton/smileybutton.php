<?php
/**
 * Name: Smileybutton
 * Description: Adds a smileybutton to the Inputbox
 * Version: 0.2
 * Author: Johannes Schwab <https://friendica.jschwab.org/profile/ddorian>
 */
use Friendica\Content\Text;
use Friendica\Core\Addon;

function smileybutton_install() {
	//Register hooks
	Addon::registerHook('jot_tool', 'addon/smileybutton/smileybutton.php', 'show_button');

	Text::logger("installed smileybutton");
}


function smileybutton_uninstall() {
	//Delet registered hooks
	Addon::unregisterHook('jot_tool',    'addon/smileybutton/smileybutton.php', 'show_button');

	Text::logger("removed smileybutton");
}



function show_button(Friendica\App $a, &$b) {
	// Disable if theme is quattro
	// TODO add style for quattro
	if ($a->getCurrentTheme() == 'quattro')
		return;

	// Disable for mobile because most mobiles have a smiley key for ther own
	if ($a->is_mobile || $a->is_tablet)
		return;

	/**
	 *
 	 * I have copied this from src/Content/Text.php, removed doubles
	 * and escaped them.
	 *
	 */

	$texts =  [
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

	];

	$icons = [
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-heart.gif" alt="<3" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-brokenheart.gif" alt="</3" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-smile.gif" alt=":-)" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-wink.gif" alt=";-)" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-frown.gif" alt=":-(" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-tongue-out.gif" alt=":-P" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-kiss.gif" alt=":-X" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-laughing.gif" alt=":-D" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-surprised.gif" alt=":-O" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-thumbsup.gif" alt="\\o/" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-Oo.gif" alt="O_o" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-cry.gif" alt=":\'(" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-foot-in-mouth.gif" alt=":-!" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-undecided.gif" alt=":-/" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-embarassed.gif" alt=":-[" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-cool.gif" alt="8-)" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/beer_mug.gif" alt=":beer" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/coffee.gif" alt=":coffee" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/smiley-facepalm.gif" alt=":facepalm" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/like.gif" alt=":like" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/dislike.gif" alt=":dislike" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/friendica-16.png" alt="~friendica" />',
		'<img class="smiley" src="' . $a->getBaseURL() . '/images/rhash-16.png" alt="red" />'
	];

	// Call hooks to get aditional smileies from other addons
	$params = ['texts' => $texts, 'icons' => $icons, 'string' => ""]; //changed
	Addon::callHooks('smilie', $params);

	//Generate html for smiley list
	$s = "<table class=\"smiley-preview\"><tr>\n\t";
	for($x = 0; $x < count($params['texts']); $x ++) {
		$icon = $params['icons'][$x];
		$icon = str_replace('/>', 'onclick="smileybutton_addsmiley(\'' . $params['texts'][$x] . '\')"/>', $icon);
		$icon = str_replace('class="smiley"', 'class="smiley_preview"', $icon);
		$s .= "<td>" . $icon . "</td>";
		if (($x+1) % (sqrt(count($params['texts']))+1) == 0) {
			$s .= "</tr>\n\t<tr>";
		}
	}
	$s .= "\t</tr></table>";

	//Add css to header
	$css_file = 'addon/smileybutton/view/' . $a->getCurrentTheme() . '.css';
	if (! file_exists($css_file))
		$css_file = 'addon/smileybutton/view/default.css';
	$css_url = $a->getBaseURL().'/'.$css_file;

	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$css_url.'" media="all" />'."\r\n";


	//Get the correct image for the theme
	$image = 'addon/smileybutton/view/' . $a->getCurrentTheme() . '.png';
	if (! file_exists($image))
		$image = 'addon/smileybutton/view/default.png';
	$image_url = $a->getBaseURL().'/'.$image;

	//Add the hmtl and script to the page
	$b = <<< EOT
	<div id="profile-smiley-wrapper" style="display: block;" >
		<img src="$image_url" class="smiley_button" onclick="toggle_smileybutton()" alt="smiley">
		<div id="smileybutton" style="display:none;">
		$s
		</div>
	</div>

	<script>
		var smileybutton_is_shown = 0;
		function toggle_smileybutton() {
			if (! smileybutton_is_shown) {
				$("#smileybutton").show();
				smileybutton_is_shown = 1;
			} else {
				$("#smileybutton").hide();
				smileybutton_is_shown = 0;
			}
		}

		function smileybutton_addsmiley(text) {
			if(plaintext == "none") {
				var v = $("#profile-jot-text").val();
				v = v + text;
				$("#profile-jot-text").val(v);
				$("#profile-jot-text").focus();
			} else {
				var v = tinymce.activeEditor.getContent();
				v = v + text;
				tinymce.activeEditor.setContent(v);
				tinymce.activeEditor.focus();
			}
		}
	</script>
EOT;
}
