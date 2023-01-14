<?php
/**
 * Name: Show More
 * Description: Collapse posts
 * Version: 1.0
 * Author: Michael Vogel <ike@piratenpartei.de>
 *         based upon NSFW from Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Util\Strings;

function showmore_install()
{
	Hook::register('prepare_body', 'addon/showmore/showmore.php', 'showmore_prepare_body');
	Hook::register('addon_settings', 'addon/showmore/showmore.php', 'showmore_addon_settings');
	Hook::register('addon_settings_post', 'addon/showmore/showmore.php', 'showmore_addon_settings_post');
}

function showmore_addon_settings(array &$data)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	DI::page()->registerStylesheet(__DIR__ . '/showmore.css', 'all');

	$enabled = !DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'showmore', 'disable');
	$chars   = DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'showmore', 'chars', 1100);

	$t    = Renderer::getMarkupTemplate('settings.tpl', 'addon/showmore/');
	$html = Renderer::replaceMacros($t, [
		'$enabled' => ['showmore-enable', DI::l10n()->t('Enable Show More'), $enabled],
		'$chars'   => ['showmore-chars', DI::l10n()->t('Cutting posts after how many characters'), $chars],
	]);

	$data = [
		'addon' => 'showmore',
		'title' => DI::l10n()->t('"Show more" Settings'),
		'html'  => $html,
	];
}

function showmore_addon_settings_post(array &$b)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	if (!empty($_POST['showmore-submit'])) {
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'showmore', 'chars', trim($_POST['showmore-chars']));
		$enable = (!empty($_POST['showmore-enable']) ? intval($_POST['showmore-enable']) : 0);
		$disable = 1-$enable;
		DI::pConfig()->set(DI::userSession()->getLocalUserId(), 'showmore', 'disable', $disable);
	}
}

function get_body_length($body)
{
	$string = trim($body);

	// DomDocument doesn't like empty strings
	if (!strlen($string)) {
		return 0;
	}

	// We need to get rid of hidden tags (display: none)

	// Get rid of the warning. It would be better to have some valid html as input
	$doc = new DOMDocument();
	@$doc->loadHTML($body);
	$xpath = new DOMXPath($doc);

	/*
	 * Checking any possible syntax of the style attribute with xpath is impossible
	 * So we just get any element with a style attribute, and check them with a regexp
	 */
	$xr = $xpath->query('//*[@style]');
	foreach ($xr as $node) {
		if (preg_match('/.*display: *none *;.*/',$node->getAttribute('style'))) {
			// Hidden, remove it from its parent
			$node->parentNode->removeChild($node);
		}
	}
	// Now we can get the body of our HTML DomDocument, it contains only what is visible
	$string = $doc->saveHTML();

	$string = strip_tags($string);
	return strlen($string);
}

function showmore_prepare_body(&$hook_data)
{
	// No combination with content filters
	if (!empty($hook_data['filter_reasons'])) {
		return;
	}

	if (DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'showmore', 'disable')) {
		return;
	}

	$chars = (int) DI::pConfig()->get(DI::userSession()->getLocalUserId(), 'showmore', 'chars', 1100);

	if (get_body_length($hook_data['html']) > $chars) {
		$found = true;
		$shortened = trim(showmore_cutitem($hook_data['html'], $chars)) . "...";
	} else {
		$found = false;
	}

	if ($found) {
		$rnd = Strings::getRandomHex(8);
		$hook_data['html'] = '<span id="showmore-teaser-' . $rnd . '" class="showmore-teaser" style="display: block;" aria-hidden="true" dir="auto">' . $shortened . " " .
			'<span id="showmore-wrap-' . $rnd . '" style="white-space:nowrap;" class="showmore-wrap fakelink" onclick="openClose(\'showmore-' . $rnd . '\'); openClose(\'showmore-teaser-' . $rnd . '\');">' . DI::l10n()->t('show more') . '</span></span>' .
			'<div id="showmore-' . $rnd . '" class="showmore-content" style="display: none;" aria-hidden="false" dir="auto">' . $hook_data['html'] . '</div>';
	}
}

function showmore_cutitem($text, $limit)
{
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

	return $text;
}
