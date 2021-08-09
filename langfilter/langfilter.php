<?php
/*
 * Name: Language Filter
 * Version: 0.1
 * Description: Filters out postings in languages not spoken by the users
 * Author: Tobias Diekershoff <https://f.diekershoff.de/u/tobias>
 * License: MIT
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

/* Define the hooks we want to use
 * that is, we have settings, we need to save the settings and we want
 * to modify the content of a posting when friendica prepares it.
 */

function langfilter_install()
{
	Hook::register('prepare_body_content_filter', 'addon/langfilter/langfilter.php', 'langfilter_prepare_body_content_filter', 10);
	Hook::register('addon_settings', 'addon/langfilter/langfilter.php', 'langfilter_addon_settings');
	Hook::register('addon_settings_post', 'addon/langfilter/langfilter.php', 'langfilter_addon_settings_post');
}

/* The settings
 * 1st check if somebody logged in is calling
 * 2nd get the current settings
 * 3rd parse a SMARTY3 template, replacing some translateable strings for the form
 */

function langfilter_addon_settings(App $a, &$s)
{
	if (!local_user()) {
		return;
	}

	$enabled = DI::pConfig()->get(local_user(), 'langfilter', 'enable',
		!DI::pConfig()->get(local_user(), 'langfilter', 'disable'));

	$enable_checked = $enabled ? ' checked="checked"' : '';
	$languages      = DI::pConfig()->get(local_user(), 'langfilter', 'languages');
	$minconfidence  = DI::pConfig()->get(local_user(), 'langfilter', 'minconfidence', 0) * 100;
	$minlength      = DI::pConfig()->get(local_user(), 'langfilter', 'minlength'    , 32);

	$t = Renderer::getMarkupTemplate("settings.tpl", "addon/langfilter/");
	$s .= Renderer::replaceMacros($t, [
		'$title'         => DI::l10n()->t("Language Filter"),
		'$intro'         => DI::l10n()->t('This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'),
		'$enabled'       => ['langfilter_enable', DI::l10n()->t('Use the language filter'), $enable_checked, ''],
		'$languages'     => ['langfilter_languages', DI::l10n()->t('Able to read'), $languages, DI::l10n()->t('List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".')],
		'$minconfidence' => ['langfilter_minconfidence', DI::l10n()->t('Minimum confidence in language detection'), $minconfidence, DI::l10n()->t('Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.')],
		'$minlength'     => ['langfilter_minlength', DI::l10n()->t('Minimum length of message body'), $minlength, DI::l10n()->t('Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).')],
		'$submit'        => DI::l10n()->t('Save Settings'),
	]);

	return;
}

/* Save the settings
 * 1st check it's a logged in user calling
 * 2nd check the langfilter form is to be saved
 * 3rd save the settings to the DB for later usage
 */

function langfilter_addon_settings_post(App $a, &$b)
{
	if (!local_user()) {
		return;
	}

	if (!empty($_POST['langfilter-settings-submit'])) {
		$enable        = intval($_POST['langfilter_enable'] ?? 0);
		$languages     = trim($_POST['langfilter_languages'] ?? '');
		$minconfidence = max(0, min(100, intval($_POST['langfilter_minconfidence'] ?? 0))) / 100;
		$minlength     = intval($_POST['langfilter_minlength'] ?? 32);
		if ($minlength <= 0) {
			$minlength = 32;
		}

		DI::pConfig()->set(local_user(), 'langfilter', 'enable'       , $enable);
		DI::pConfig()->set(local_user(), 'langfilter', 'languages'    , $languages);
		DI::pConfig()->set(local_user(), 'langfilter', 'minconfidence', $minconfidence);
		DI::pConfig()->set(local_user(), 'langfilter', 'minlength'    , $minlength);
	}
}

/* Actually filter postings by their language
 * 1st check if the user wants to filter postings
 * 2nd get the user settings which languages shall be not filtered out
 * 3rd extract the language of a posting
 * 4th if the determined language does not fit to the spoken languages
 *     of the user, then collapse the posting, but provide a link to
 *     expand it again.
 */

function langfilter_prepare_body_content_filter(App $a, &$hook_data)
{
	$logged_user = local_user();
	if (!$logged_user) {
		return;
	}

	// Never filter own messages
	// TODO: find a better way to extract this
	$logged_user_profile = DI::baseUrl()->get() . '/profile/' . $a->getUserNickname();
	if ($logged_user_profile == $hook_data['item']['author-link']) {
		return;
	}

	// Don't filter if language filter is disabled
	if (!DI::pConfig()->get($logged_user, 'langfilter', 'enable',
		!DI::pConfig()->get($logged_user, 'langfilter', 'disable'))
	) {
		return;
	}

	if (!empty($hook_data['item']['rendered-html'])) {
		$naked_body = strip_tags($hook_data['item']['rendered-html']);
	} else {
		$naked_body = BBCode::toPlaintext($hook_data['item']['body'], false);
	}

	// Don't filter if body lenght is below minimum
	$minlen = DI::pConfig()->get(local_user(), 'langfilter', 'minlength', 32);
	if (!$minlen) {
		$minlen = 32;
	}

	if (strlen($naked_body) < $minlen) {
		return;
	}

	$read_languages_string = DI::pConfig()->get(local_user(), 'langfilter', 'languages');
	$minconfidence = DI::pConfig()->get(local_user(), 'langfilter', 'minconfidence');

	// Don't filter if no spoken languages are configured
	if (!$read_languages_string) {
		return;
	}
	$read_languages_array = explode(',', $read_languages_string);

	$iso639 = new Matriphe\ISO639\ISO639;

	// Extract the language of the post
	if (!empty($hook_data['item']['language'])) {
		$languages = json_decode($hook_data['item']['language'], true);
		if (!is_array($languages)) {
			return;
		}

		foreach ($languages as $iso2 => $confidence) {
			break;
		}

		if (empty($iso2)) {
			return;
		}

		$lang = $iso639->languageByCode1($iso2);
	} else {
		$opts = $hook_data['item']['postopts'];
		if (!$opts) {
			// no options associated to post
			return;
		}

		if (!preg_match('/\blang=([^;]*);([^:]*)/', $opts, $matches)) {
			// no lang options associated to post
			return;
		}

		$lang = $matches[1];
		$confidence = $matches[2];

		$iso2 = $iso639->code1ByLanguage($lang);
	}

	// Do not filter if language detection confidence is too low
	if ($minconfidence && $confidence < $minconfidence) {
		return;
	}

	if (!$iso2) {
		return;
	}

	if (!in_array($iso2, $read_languages_array)) {
		$hook_data['filter_reasons'][] = DI::l10n()->t('Filtered language: %s', ucfirst($lang));
	}
}
