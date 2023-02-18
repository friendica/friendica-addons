<?php
/**
 * Name: Smileybutton
 * Description: Adds a smileybutton to the Inputbox
 * Version: 1.0
 * Author: Johannes Schwab <https://friendica.jschwab.org/profile/ddorian>
 * Maintainer: Hypolite Petovan <https://friendica.mrpetovan.com/profile/hypolite>
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

function smileybutton_install()
{
	//Register hooks
	Hook::register('jot_tool', 'addon/smileybutton/smileybutton.php', 'smileybutton_jot_tool');
}

function smileybutton_jot_tool(string &$body)
{
	// Disable if theme is quattro
	if (DI::app()->getCurrentTheme() == 'quattro') {
		return;
	}

	// Disable for mobile because they have a smiley key of their own
	if (DI::mode()->isMobile() || DI::mode()->isMobile()) {
		return;
	}

	$texts = [
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
		':\'(',
		':-!',
		':-/',
		':-[',
		'8-)',
		':beer',
		':coffee',
		':facepalm',
		':like',
		':dislike',
		'~friendica',
		'red#',
	];

	$icons = [
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-heart.gif" alt="<3" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-brokenheart.gif" alt="</3" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-smile.gif" alt=":-)" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-wink.gif" alt=";-)" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-frown.gif" alt=":-(" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-tongue-out.gif" alt=":-P" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-kiss.gif" alt=":-X" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-laughing.gif" alt=":-D" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-surprised.gif" alt=":-O" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-thumbsup.gif" alt="\\o/" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-Oo.gif" alt="O_o" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-cry.gif" alt=":\'(" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-foot-in-mouth.gif" alt=":-!" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-undecided.gif" alt=":-/" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-embarassed.gif" alt=":-[" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-cool.gif" alt="8-)" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/beer_mug.gif" alt=":beer" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/coffee.gif" alt=":coffee" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/smiley-facepalm.gif" alt=":facepalm" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/like.gif" alt=":like" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/dislike.gif" alt=":dislike" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/friendica-16.png" alt="~friendica" />',
		'<img class="smiley" src="' . DI::baseUrl() . '/images/rhash-16.png" alt="red" />'
	];

	// Call hooks to get aditional smileies from other addons
	$params = ['texts' => $texts, 'icons' => $icons, 'string' => '']; //changed
	Hook::callAll('smilie', $params);

	//Generate html for smiley list
	$s = '<table class="smiley-preview"><tr>';
	for ($x = 0; $x < count($params['texts']); $x++) {
		$icon = $params['icons'][$x];
		$s .= '<td onclick="smileybutton_addsmiley(\'' . $params['texts'][$x] . '\')">' . $icon . '</td>';
		if (($x + 1) % (sqrt(count($params['texts'])) + 1) == 0) {
			$s .= '</tr><tr>';
		}
	}
	$s .= '</tr></table>';

	//Add css to header
	$css_file = __DIR__ . '/view/' . DI::app()->getCurrentTheme() . '.css';
	if (!file_exists($css_file)) {
		$css_file = __DIR__ . '/view/default.css';
	}

	DI::page()->registerStylesheet($css_file);

	//Get the correct image for the theme
	$image = 'addon/smileybutton/view/' . DI::app()->getCurrentTheme() . '.png';
	if (!file_exists($image)) {
		$image = 'addon/smileybutton/view/default.png';
	}

	$image_url = DI::baseUrl() . '/' . $image;

	//Add the hmtl and script to the page
	$body = <<< EOT
	<div id="profile-smiley-wrapper">
		<button type="button" class="btn btn-link smiley_button" onclick="toggle_smileybutton()"><img src="$image_url" alt="smiley"></button>
		<div id="smileybutton">
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
			var v = $("#profile-jot-text").val();
			v = v + text;
			$("#profile-jot-text").val(v).focus();
		}
	</script>
EOT;
}

