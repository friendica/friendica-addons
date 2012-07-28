<?php
/**
* Name: Infinite Improbability Drive
* Description: Infinitely Improbably Find A Random User
* Version: 1.0
* Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
*/

function infiniteimprobabilitydrive_install() {
register_hook('app_menu', 'addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.php', 'infiniteimprobabilitydrive_app_menu');
}

function infiniteimprobabilitydrive_uninstall() {
unregister_hook('app_menu', 'addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.php', 'infiniteimprobabilitydrive_app_menu');

}

function infiniteimprobabilitydrive_app_menu($a,&$b) {
$b['app_menu'][] = '<div class="app-title"><a href="infiniteimprobabilitydrive">' . t('Infinite Improbability Drive') . '</a></div>';
}


function infiniteimprobabilitydrive_module() {
return;
}


function infiniteimprobabilitydrive_content(&$a) {
$baseurl = $a->get_baseurl() . '/addon/infiniteimprobabilitydrive';
$o = '';

$a->page['htmlhead'] .= '<link rel="stylesheet" type="text/css" href="'.$a->get_baseurl().'/addon/infiniteimprobabilitydrive/infiniteimprobabilitydrive.css"/>';


$baseurl = $a->get_baseurl();


  $o .= <<< EOT

<br><br>
<p>Try another destination with the <a href="$baseurl/infiniteimprobabilitydrive">Infinite Improbability Drive</a>
<iframe src ="$baseurl/randprof" height="1200" width="1024">



EOT;
return $o;
    
}