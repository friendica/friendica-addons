<?php
/**
 * Name: blackout
 * Description: Blackout your ~friendica node during a given period, requires PHP >= 5.3
 * License: MIT
 * Version: 1.0
 * Author: Tobias Diekershoff <https://f.diekershoff.de/~tobias>
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
 * Requirements
 * ============
 *
 * THIS ADDON REQUIRES PHP VERSION 5.3 OR HIGHER.
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
use Friendica\Core\Addon;
use Friendica\Core\L10n;

function blackout_install() {
    Addon::registerHook('page_header', 'addon/blackout/blackout.php', 'blackout_redirect');
}

function blackout_uninstall() {
    Addon::unregisterHook('page_header', 'addon/blackout/blackout.php', 'blackout_redirect');
}
function blackout_redirect ($a, $b) {
    // if we have a logged in user, don't throw her out
    if (local_user()) {
        return true;
    }

	if (! (version_compare(PHP_VERSION, '5.3.0') >= 0))
		return true;

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
        logger('redirecting user to blackout page');
        goaway($myurl);
    }
}

function blackout_addon_admin(&$a, &$o) {
    $mystart = Config::get('blackout','begindate');
    if (! is_string($mystart)) { $mystart = "YYYY-MM-DD:hhmm"; }
    $myend   = Config::get('blackout','enddate');
    if (! is_string($myend)) { $myend = "YYYY-MM-DD:hhmm"; }
    $myurl   = Config::get('blackout','url');
    if (! is_string($myurl)) { $myurl = "http://www.example.com"; }
    $t = get_markup_template( "admin.tpl", "addon/blackout/" );

   $o = replace_macros($t, [
        '$submit' => L10n::t('Save Settings'),
        '$rurl' => ["rurl", "Redirect URL", $myurl, "all your visitors from the web will be redirected to this URL"],
        '$startdate' => ["startdate", "Begin of the Blackout<br />(YYYY-MM-DD hh:mm)", $mystart, "format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute"],
        '$enddate' => ["enddate", "End of the Blackout<br />(YYYY-MM-DD hh:mm)", $myend, ""],

    ]);
    $date1 = DateTime::createFromFormat('Y-m-d G:i', $mystart);
    $date2 = DateTime::createFromFormat('Y-m-d G:i', $myend);
    if ($date2 < $date1) {
        $o = "<div style='border: 2px solid #f00; bakckground: #b00; text-align: center; padding: 10px; margin: 30px;'>The end-date is prior to the start-date of the blackout, you should fix this.</div>" . $o;
    } else {
        $o = '<p>Please double check that the current settings for the blackout. Begin will be <strong>'.$mystart.'</strong> and it will end <strong>'.$myend.'</strong>.</p>' . $o;
    }
}
function blackout_addon_admin_post (&$a) {
    $begindate = trim($_POST['startdate']);
    $enddate = trim($_POST['enddate']);
    $url = trim($_POST['rurl']);
    Config::set('blackout','begindate',$begindate);
    Config::set('blackout','enddate',$enddate);
    Config::set('blackout','url',$url);
}
