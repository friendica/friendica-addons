<?php
/*
 * Name: Adult Smilies
 * Description: Smily icons that could or should not be included in core
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 * This is a template for how to extend the "smily" code.
 * 
 */

function smilies_adult_install() {
	register_hook('smilie', 'addon/smilies_adult/smilies_adult.php', 'smilies_adult_smilies');
}

function smilies_adult_uninstall() {
	unregister_hook('smilie', 'addon/smilies_adult/smilies_adult.php', 'smilies_adult_smilies');
}

 

function smilies_adult_smilies(&$a,&$b) {

	$b['texts'][] = '(o)(o)';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smilies-adult/icons/tits.gif' . '" alt="' . '(o)(o)' . '" />';

	$b['texts'][] = '(.)(.)';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smilies-adult/icons/tits.gif' . '" alt="' . '(.)(.)' . '" />';

	$b['texts'][] = ':bong';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smilies-adult/icons/bong.gif' . '" alt="' . ':bong' . '" />';

	$b['texts'][] = ':sperm';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smilies-adult/icons/sperm.gif' . '" alt="' . ':sperm' . '" />';

	$b['texts'][] = ':drunk';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/smilies-adult/icons/drunk.gif' . '" alt="' . ':drunk' . '" />';
}