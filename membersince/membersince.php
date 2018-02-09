<?php
/**
 * Name: MemberSince
 * Description: Display membership date in profile
 * Version: 1.1
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

use Friendica\Core\Addon;
use Friendica\Core\L10n;
use Friendica\Util\DateTimeFormat;

function membersince_install()
{
	Addon::registerHook('profile_advanced', 'addon/membersince/membersince.php', 'membersince_display');
}

function membersince_uninstall()
{
	Addon::unregisterHook('profile_advanced', 'addon/membersince/membersince.php', 'membersince_display');
}

function membersince_display(&$a, &$b)
{
	if (current_theme() == 'frio') {
		// Works in Frio.
		$html = '<!DOCTYPE html><html><body>' . $b . '</body></html>';

		$doc = new DOMDocument();
		$doc->validateOnParse = true;
		@$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

		$elm = $doc->getElementById('aprofile-fullname');

		$div = $doc->createElement('div');
		$div->setAttribute('id','aprofile-membersince');
		$div->setAttribute('class','col-lg-12 col-md-12 col-sm-12 col-xs-12 aprofile');

		// The seperator line.
		$hr = $doc->createElement('hr','');
		$hr->setAttribute('class','profile-separator');

		// The label div.
		$label = $doc->createElement('div', L10n::t('Member since:'));
		$label->setAttribute('class', 'col-lg-4 col-md-4 col-sm-4 col-xs-12 profile-label-name text-muted');

		// The div for the register date of the profile owner.
		$entry = $doc->createElement('div', DateTimeFormat::local($a->profile['register_date']));
		$entry->setAttribute('class', 'col-lg-8 col-md-8 col-sm-8 col-xs-12 profile-entry');

		$div->appendChild($hr);
		$div->appendChild($label);
		$div->appendChild($entry);
		$elm->parentNode->insertBefore($div, $elm->nextSibling);

		$b = $doc->saveHTML();
	} else {
		// Works in Vier.
		$b = preg_replace('/<\/dl>/', "</dl>\n\n\n<dl id=\"aprofile-membersince\" class=\"aprofile\">\n<dt>" . L10n::t('Member since:') . "</dt>\n<dd>" . DateTimeFormat::local($a->profile['register_date']) . "</dd>\n</dl>", $b, 1);
	}
}
