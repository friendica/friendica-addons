<?php
/**
 * Name: Show More
 * Description: Collapse posts
 * Version: 1.0
 * Author: Michael Vogel <ike@piratenpartei.de>
 *         based upon NSFW from Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\PConfig;

function showmore_install() {
	Addon::registerHook('prepare_body', 'addon/showmore/showmore.php', 'showmore_prepare_body');
	Addon::registerHook('addon_settings', 'addon/showmore/showmore.php', 'showmore_addon_settings');
	Addon::registerHook('addon_settings_post', 'addon/showmore/showmore.php', 'showmore_addon_settings_post');
}

function showmore_uninstall() {
	Addon::unregisterHook('prepare_body', 'addon/showmore/showmore.php', 'showmore_prepare_body');
	Addon::unregisterHook('addon_settings', 'addon/showmore/showmore.php', 'showmore_addon_settings');
	Addon::unregisterHook('addon_settings_post', 'addon/showmore/showmore.php', 'showmore_addon_settings_post');
}

function showmore_addon_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$a->get_baseurl().'/addon/showmore/showmore.css'.'" media="all"/>'."\r\n";

	$enable_checked = (intval(PConfig::get(local_user(),'showmore','disable')) ? '' : ' checked="checked"');
	$chars = PConfig::get(local_user(),'showmore','chars');
	if(!$chars)
		$chars = '1100';

	$s .= '<span id="settings_showmore_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_showmore_expanded\'); openClose(\'settings_showmore_inflated\');">';
	$s .= '<h3>' . t('"Show more" Settings').'</h3>';
	$s .= '</span>';
	$s .= '<div id="settings_showmore_expanded" class="settings-block" style="display: none;">';
	$s .= '<span class="fakelink" onclick="openClose(\'settings_showmore_expanded\'); openClose(\'settings_showmore_inflated\');">';
	$s .= '<h3>' . t('"Show more" Settings').'</h3>';
	$s .= '</span>';

	$s .= '<div id="showmore-wrapper">';

	$s .= '<label id="showmore-enable-label" for="showmore-enable">'.t('Enable Show More').'</label>';
	$s .= '<input id="showmore-enable" type="checkbox" name="showmore-enable" value="1"'.$enable_checked.' />';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="showmore-label" for="showmore-chars">'.t('Cutting posts after how much characters').' </label>';
	$s .= '<input id="showmore-words" type="text" name="showmore-chars" value="'.$chars.'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="showmore-submit" name="showmore-submit" class="settings-submit" value="' . t('Save Settings') . '" /></div>';
//	$s .= '<div class="showmore-desc">' . t('Use /expression/ to provide regular expressions') . '</div>';
	$s .= '</div>';

	return;
}

function showmore_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['showmore-submit']) {
		PConfig::set(local_user(),'showmore','chars',trim($_POST['showmore-chars']));
		$enable = ((x($_POST,'showmore-enable')) ? intval($_POST['showmore-enable']) : 0);
		$disable = 1-$enable;
		PConfig::set(local_user(),'showmore','disable', $disable);
		info( t('Show More Settings saved.') . EOL);
	}
}

function get_body_length($body) {
	$string = trim($body);

	// DomDocument doesn't like empty strings
	if(! strlen($string)) {
		return 0;
	}

	// We need to get rid of hidden tags (display: none)

	// Get rid of the warning. It would be better to have some valid html as input
	$dom = @DomDocument::loadHTML($body);
	$xpath = new DOMXPath($dom);

	/*
	 * Checking any possible syntax of the style attribute with xpath is impossible
	 * So we just get any element with a style attribute, and check them with a regexp
	 */
	$xr = $xpath->query('//*[@style]');
	foreach($xr as $node) {
		if(preg_match('/.*display: *none *;.*/',$node->getAttribute('style'))) {
			// Hidden, remove it from its parent
			$node->parentNode->removeChild($node);
		}
	}
	// Now we can get the body of our HTML DomDocument, it contains only what is visible
	$string = $dom->saveHTML();

	$string = strip_tags($string);
	return strlen($string);
}

function showmore_prepare_body(&$a,&$b) {

	$words = null;
	if(PConfig::get(local_user(),'showmore','disable'))
		return;

	$chars = (int)PConfig::get(local_user(),'showmore','chars');
	if(!$chars)
		$chars = 1100;

	if (get_body_length($b['html']) > $chars) {
		$found = true;
		$shortened = trim(showmore_cutitem($b['html'], $chars))."...";
	}

	if($found) {
		$rnd = random_string(8);
		$b['html'] = '<span id="showmore-teaser-'.$rnd.'" class="showmore-teaser" style="display: block;">'.$shortened." ".
				'<span id="showmore-wrap-'.$rnd.'" style="white-space:nowrap;" class="showmore-wrap fakelink" onclick="openClose(\'showmore-'.$rnd.'\'); openClose(\'showmore-teaser-'.$rnd.'\');" >'.sprintf(t('show more')).'</span></span>'.
				'<div id="showmore-'.$rnd.'" class="showmore-content" style="display: none;">'.$b['html'].'</div>';
	}
}

function showmore_cutitem($text, $limit) {
	$text = trim($text);

	$text = mb_convert_encoding($text, 'HTML-ENTITIES', "UTF-8");

	$text = substr($text, 0, $limit);

	$pos1 = strrpos($text, "<");
	$pos2 = strrpos($text, ">");
	$pos3 = strrpos($text, "&");
	$pos4 = strrpos($text, ";");

	if ($pos1 > $pos3) {
		if ($pos1 > $pos2)
			$text = substr($text, 0, $pos1);
	} else {
		if ($pos3 > $pos4)
			$text = substr($text, 0, $pos3);
	}

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;

	$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">';
	@$doc->loadHTML($doctype."<html><body>".$text."</body></html>");

	$text = $doc->saveHTML();
	$text = str_replace(["<html><body>", "</body></html>", $doctype], ["", "", ""], $text);

	return($text);
}
