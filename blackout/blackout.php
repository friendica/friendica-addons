<?php
/**
 * Name: blackout
 * Description: Blackout your ~friendica node during a given period
 * License: MIT
 * Version: 1.1
 * Author: Tobias Diekershoff <https://social.diekershoff.de/~tobias>
 *
 * About
 * =====
 *
 * This addon will allow you to enter a date/time period during which
 * all your ~friendica visitors from the web will be redirected to a page
 * you can configure in the admin panel as well.
 *
 * Calls to the API and the communication with other ~friendica nodes is
 * not effected from this addon.
 *
 * If you enter a period the current date would be affected none of the
 * currently logged in users will be effected as well. But if they log
 * out they can't login again. That way you dear admin can double check
 * the entered time periode and fix typos without having to hack the
 * database directly.
 *
 * License
 * =======
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

use Friendica\Core\Config;
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Core\System;

function blackout_install() {
	Hook::register('page_header', 'addon/blackout/blackout.php', 'blackout_redirect');
}

function blackout_uninstall() {
	Hook::unregister('page_header', 'addon/blackout/blackout.php', 'blackout_redirect');
}
function blackout_redirect ($a, $b) {
	// if we have a logged in user, don't throw her out
	if (local_user()) {
		return true;
	}

	// else...
	$mystart = Config::get('blackout','begindate');
	$myend   = Config::get('blackout','enddate');
	$myurl   = Config::get('blackout','url');
	$now = time();
	$date1 = DateTime::createFromFormat('Y-m-d G:i', $mystart);
	$date2 = DateTime::createFromFormat('Y-m-d G:i', $myend);
	if ( $date1 && $date2 ) {
		$date1 = DateTime::createFromFormat('Y-m-d G:i', $mystart)->format('U');
		$date2 = DateTime::createFromFormat('Y-m-d G:i', $myend)->format('U');
	} else {
		   $date1 = 0;
		   $date2 = 0;
	}
	if (( $date1 <= $now ) && ( $now <= $date2 )) {
		Logger::log('redirecting user to blackout page');
		System::externalRedirect($myurl);
	}
}

function blackout_addon_admin(&$a, &$o) {
	$mystart = Config::get('blackout','begindate');
	if (! is_string($mystart)) { $mystart = "YYYY-MM-DD hh:mm"; }
	$myend   = Config::get('blackout','enddate');
	if (! is_string($myend)) { $myend = "YYYY-MM-DD hh:mm"; }
	$myurl   = Config::get('blackout','url');
	if (! is_string($myurl)) { $myurl = "https://www.example.com"; }
	$t = Renderer::getMarkupTemplate( "admin.tpl", "addon/blackout/" );

	$date1 = DateTime::createFromFormat('Y-m-d G:i', $mystart);
	$date2 = DateTime::createFromFormat('Y-m-d G:i', $myend);
	// a note for the admin
	$adminnote = "";
	if ($date2 < $date1) {
		$adminnote = DI::l10n()->t("The end-date is prior to the start-date of the blackout, you should fix this");
	} else {
		$adminnote = DI::l10n()->t("Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>.", $mystart, $myend);
	}
	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$rurl' => ["rurl", DI::l10n()->t("Redirect URL"), $myurl, DI::l10n()->t("all your visitors from the web will be redirected to this URL"), "", "", "url"],
		'$startdate' => ["startdate", DI::l10n()->t("Begin of the Blackout"), $mystart, DI::l10n()->t("Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.")],
		'$enddate' => ["enddate", DI::l10n()->t("End of the Blackout"), $myend, ""],
		'$adminnote' => $adminnote,
		'$aboutredirect' => DI::l10n()->t("<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."),
	]);
}
function blackout_addon_admin_post (&$a) {
	$begindate = trim($_POST['startdate']);
	$enddate = trim($_POST['enddate']);
	$url = trim($_POST['rurl']);
	Config::set('blackout','begindate',$begindate);
	Config::set('blackout','enddate',$enddate);
	Config::set('blackout','url',$url);
}
