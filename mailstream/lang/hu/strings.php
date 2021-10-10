<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['From Address'] = 'Feladócím';
$a->strings['Email address that stream items will appear to be from.'] = 'E-mail-cím, ahonnan úgy tűnik, hogy a folyam elemei származnak.';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Re:'] = 'Vá:';
$a->strings['Friendica post'] = 'Friendica-bejegyzés';
$a->strings['Diaspora post'] = 'Diaspora-bejegyzés';
$a->strings['Feed item'] = 'Hírforráselem';
$a->strings['Email'] = 'E-mail';
$a->strings['Friendica Item'] = 'Friendica-elem';
$a->strings['Upstream'] = 'Távoli';
$a->strings['Local'] = 'Helyi';
$a->strings['Enabled'] = 'Engedélyezve';
$a->strings['Email Address'] = 'E-mail-cím';
$a->strings['Leave blank to use your account email address'] = 'Hagyja üresen a fiókja e-mail-címének használatához';
$a->strings['Exclude Likes'] = 'Kedvelések kizárása';
$a->strings['Check this to omit mailing "Like" notifications'] = 'Jelölje be ezt a „Tetszik” értesítések elküldésének kihagyásához';
$a->strings['Attach Images'] = 'Képek csatolása';
$a->strings['Download images in posts and attach them to the email.  Useful for reading email while offline.'] = 'Képek letöltése a bejegyzésekből és csatolás az e-mailhez. Hasznos az e-mailek kapcsolat nélküli olvasásakor.';
$a->strings['Mail Stream Settings'] = 'Levelezőfolyam beállításai';
