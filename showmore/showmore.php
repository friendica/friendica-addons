<?php
/**
 * Name: Show More
 * Description: Collapse posts
 * Version: 1.0
 * Author: Michael Vogel <ike@piratenpartei.de>
 *         based upon NSFW from Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

function showmore_install() {
	register_hook('prepare_body', 'addon/showmore/showmore.php', 'showmore_prepare_body');
	register_hook('plugin_settings', 'addon/showmore/showmore.php', 'showmore_addon_settings');
	register_hook('plugin_settings_post', 'addon/showmore/showmore.php', 'showmore_addon_settings_post');
}

function showmore_uninstall() {
	unregister_hook('prepare_body', 'addon/showmore/showmore.php', 'showmore_prepare_body');
	unregister_hook('plugin_settings', 'addon/showmore/showmore.php', 'showmore_addon_settings');
	unregister_hook('plugin_settings_post', 'addon/showmore/showmore.php', 'showmore_addon_settings_post');
}

function showmore_addon_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$a->get_baseurl().'/addon/showmore/showmore.css'.'" media="all"/>'."\r\n";

	$enable_checked = (intval(get_pconfig(local_user(),'showmore','disable')) ? '' : ' checked="checked"');
	$chars = get_pconfig(local_user(),'showmore','chars');
	if(!$chars)
		$chars = '1100';

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('"Show more" Settings').'</h3>';
	$s .= '<div id="showmore-wrapper">';

	$s .= '<label id="showmore-enable-label" for="showmore-enable">'.t('Enable Show More').'</label>';
	$s .= '<input id="showmore-enable" type="checkbox" name="showmore-enable" value="1"'.$enable_checked.' />';
	$s .= '<div class="clear"></div>';
	$s .= '<label id="showmore-label" for="showmore-chars">'.t('Cutting posts after how much characters').' </label>';
	$s .= '<input id="showmore-words" type="text" name="showmore-chars" value="'.$chars.'" />';
	$s .= '</div><div class="clear"></div>';

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" id="showmore-submit" name="showmore-submit" class="settings-submit" value="' . t('Submit') . '" /></div>';
//	$s .= '<div class="showmore-desc">' . t('Use /expression/ to provide regular expressions') . '</div></div>';

	return;
}

function showmore_addon_settings_post(&$a,&$b) {

	if(! local_user())
		return;

	if($_POST['showmore-submit']) {
		set_pconfig(local_user(),'showmore','chars',trim($_POST['showmore-chars']));
		$enable = ((x($_POST,'showmore-enable')) ? intval($_POST['showmore-enable']) : 0);
		$disable = 1-$enable;
		set_pconfig(local_user(),'showmore','disable', $disable);
		info( t('Show More Settings saved.') . EOL);
	}
}

function showmore_prepare_body(&$a,&$b) {

	$words = null;
	if(get_pconfig(local_user(),'showmore','disable'))
		return;

	$chars = (int)get_pconfig(local_user(),'showmore','chars');
	if(!$chars)
		$chars = 1100;

	if (strlen(strip_tags(trim($b['html']))) > $chars) {
		$found = true;
		$shortened = trim(showmore_cutitem($b['html'], $chars))."...";
	}

	if($found) {
		$rnd = random_string(8);
		$b['html'] = '<span id="showmore-teaser-'.$rnd.'" style="display: block;">'.$shortened." ".
				'<span id="showmore-wrap-'.$rnd.'" style="white-space:nowrap;" class="fakelink" onclick="openClose(\'showmore-'.$rnd.'\'); openClose(\'showmore-teaser-'.$rnd.'\');" >'.sprintf(t('show more')).'</span></span>'.
				'<div id="showmore-'.$rnd.'" style="display: none;">'.$b['html'].'</div>';
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
	$text = str_replace(array("<html><body>", "</body></html>", $doctype), array("", "", ""), $text);

	return($text);
}
