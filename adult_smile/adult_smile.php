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

function adult_smile_install() {
	register_hook('smilie', 'addon/adult_smile/adult_smile.php', 'adult_smile_smilies');
}

function adult_smile_uninstall() {
	unregister_hook('smilie', 'addon/adult_smile/adult_smile.php', 'adult_smile_smilies');
}

 

function adult_smile_smilies(&$a,&$b) {

	$b['texts'][] = '(o)(o)';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/adult_smile/icons/tits.gif' . '" alt="' . '(o)(o)' . '" />';

	$b['texts'][] = '(.)(.)';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/adult_smile/icons/tits.gif' . '" alt="' . '(.)(.)' . '" />';

	$b['texts'][] = ':bong';
	$b['icons'][] = '<img src="' . $a->get_baseurl() . '/addon/adult_smile/icons/bong.gif' . '" alt="' . ':bong' . '" />';


}