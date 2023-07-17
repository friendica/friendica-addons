<?php
/**
 * Name: Impressum
 * Description: Addon to add contact information to the about page (/friendica)
 * Version: 1.3
 * Author: Tobias Diekershoff <https://f.diekershoff.de/profile/tobias>
 * License: 3-clause BSD license
 */

use Friendica\App;
use Friendica\Content\Text\BBCode;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Model\User;

function impressum_install()
{
	Hook::register('load_config', 'addon/impressum/impressum.php', 'impressum_load_config');
	Hook::register('about_hook', 'addon/impressum/impressum.php', 'impressum_show');
	Hook::register('page_end', 'addon/impressum/impressum.php', 'impressum_footer');
	Logger::notice("installed impressum Addon");
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function impressum_module() {}

function impressum_content()
{
	DI::baseUrl()->redirect('friendica/');
}

function obfuscate_email (string $s): string
{
	$s = str_replace('@', '(at)', $s);
	$s = str_replace('.', '(dot)', $s);
	return $s;
}

function impressum_footer(string &$body)
{
	$text = BBCode::convertForUriId(User::getSystemUriId(), DI::config()->get('impressum', 'footer_text'));

	if ($text != '') {
		DI::page()['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="' . DI::baseUrl() . '/addon/impressum/impressum.css" media="all" />';
		$body .= '<div class="clear"></div>';
		$body .= '<div id="impressum_footer">' . $text . '</div>';
	}
}

function impressum_load_config(ConfigFileManager $loader)
{
	DI::app()->getConfigCache()->load($loader->loadAddonConfig('impressum'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function impressum_show(string &$body)
{
	$body          .= '<h3>' . DI::l10n()->t('Impressum') . '</h3>';
	$owner         = DI::config()->get('impressum', 'owner');
	$owner_profile = DI::config()->get('impressum', 'ownerprofile');
	$postal        = BBCode::convertForUriId(User::getSystemUriId(), DI::config()->get('impressum', 'postal'));
	$notes         = BBCode::convertForUriId(User::getSystemUriId(), DI::config()->get('impressum', 'notes'));

	if ($owner) {
		if ($owner_profile) {
			$tmp = '<a href="' . $owner_profile . '">' . $owner . '</a>';
		} else {
			$tmp = $owner;
		}

		if ($email = DI::config()->get('impressum', 'email')) {
			$body .= '<p><strong>' . DI::l10n()->t('Site Owner').'</strong>: ' . $tmp .'<br /><strong>' . DI::l10n()->t('Email Address') . '</strong>: ' . obfuscate_email($email) . '</p>';
		} else {
			$body .= '<p><strong>' . DI::l10n()->t('Site Owner').'</strong>: ' . $tmp .'</p>';
		}

		if ($postal) {
			$body .= '<p><strong>' . DI::l10n()->t('Postal Address') . '</strong><br />' . $postal . '</p>';
		}

		if ($notes) {
			$body .= '<p>' . $notes . '</p>';
		}
	} else {
		$body .= '<p>' . DI::l10n()->t('The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.') . '</p>';
	}
}

function impressum_addon_admin_post ()
{
	DI::config()->set('impressum', 'owner', strip_tags(trim($_POST['owner'] ?? '')));
	DI::config()->set('impressum', 'ownerprofile', strip_tags(trim($_POST['ownerprofile'] ?? '')));
	DI::config()->set('impressum', 'postal', strip_tags(trim($_POST['postal'] ?? '')));
	DI::config()->set('impressum', 'email', strip_tags(trim($_POST['email'] ?? '')));
	DI::config()->set('impressum', 'notes', strip_tags(trim($_POST['notes'] ?? '')));
	DI::config()->set('impressum', 'footer_text', strip_tags(trim($_POST['footer_text'] ?? '')));
}

function impressum_addon_admin (string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/impressum/' );
	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$owner' => ['owner', DI::l10n()->t('Site Owner'), DI::config()->get('impressum','owner'), DI::l10n()->t('The page operators name.')],
		'$ownerprofile' => ['ownerprofile', DI::l10n()->t('Site Owners Profile'), DI::config()->get('impressum','ownerprofile'), DI::l10n()->t('Profile address of the operator.')],
		'$postal' => ['postal', DI::l10n()->t('Postal Address'), DI::config()->get('impressum','postal'), DI::l10n()->t('How to contact the operator via snail mail. You can use BBCode here.')],
		'$notes' => ['notes', DI::l10n()->t('Notes'), DI::config()->get('impressum','notes'), DI::l10n()->t('Additional notes that are displayed beneath the contact information. You can use BBCode here.')],
		'$email' => ['email', DI::l10n()->t('Email Address'), DI::config()->get('impressum','email'), DI::l10n()->t('How to contact the operator via email. (will be displayed obfuscated)')],
		'$footer_text' => ['footer_text', DI::l10n()->t('Footer note'), DI::config()->get('impressum','footer_text'), DI::l10n()->t('Text for the footer. You can use BBCode here.')],
	]);
}
