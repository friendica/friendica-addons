<?php
/*
 * Name: invidious
 * Description: Replaces links to youtube.com to an invidious instance in all displays of postings on a node.
 * Version: 0.2
 * Author: Matthias Ebers <https://loma.ml/profile/feb>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function invidious_install()
{
    Hook::register('prepare_body_final', 'addon/invidious/invidious.php', 'invidious_render');
}

/* Handle the send data from the admin settings
 */
function invidious_addon_admin_post()
{
    DI::config()->set('invidious', 'server', rtrim(trim($_POST['invidiousserver']), '/'));
}

/* Hook into the admin settings to let the admin choose an
 * invidious server to use for the replacement.
 */
function invidious_addon_admin(string &$o)
{
    $invidiousserver = DI::config()->get('invidious', 'server');
    $t = Renderer::getMarkupTemplate('admin.tpl', 'addon/invidious/');
    $o = Renderer::replaceMacros($t, [
        '$settingdescription' => DI::l10n()->t('Which Invidious server shall be used for the replacements in the post bodies? Use the URL with servername and protocol. See %s for a list of available public Invidious servers.', 'https://redirect.invidious.io'),
        '$invidiousserver' => ['invidiousserver', DI::l10n()->t('Invidious server'), $invidiousserver, 'https://example.com'],
        '$submit' => DI::l10n()->t('Save Settings'),
    ]);
}

/*
 *  replace "youtube.com" with the chosen Invidious instance
 */
function invidious_render(array &$b)
{
    // this needs to be a system setting
    $replaced = false;
    $invidious = DI::config()->get('invidious', 'server', 'https://invidio.us');
    if (strstr($b['html'], 'https://www.youtube.com')) {
        $b['html'] = str_replace(['https://www.youtube.com', 'https://youtube.com'], $invidious, $b['html']);
        $replaced = true;
    }
    if ($replaced) {
        $b['html'] .= '<hr><p><small>' . DI::l10n()->t('(Invidious addon enabled: YouTube links via %s)', $invidious) . '</small></p>';
    }
}
