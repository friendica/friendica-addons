<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Administrator'] = 'Administrátor';
$a->strings['Your account on %s will expire in a few days.'] = 'Platnost Vašeho účtu na %s vyprší během několika dní.';
$a->strings['Your Friendica test account is about to expire.'] = 'Váš Friendica testovací účet brzy vyprší.';
$a->strings["Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at http://dir.friendica.com/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at http://friendica.com."] = "Ahoj %1\$s,\n\nplatnost Vašeho testovacího účtu na %2\$s vyprší za méně než 5 dní. Doufáme, že jste si testovací jízdu užili a že se Vám povedlo najít trvalý Friendica server pro Vaši integrovanou sociální komunikaci. List veřejně dostupných serverů je k dispozici na http://dir.friendica.com/siteinfo - a pro více informací, jak si vytvořit svůj vlastní server, navštivte stránky projektu Friendica na adrese http://friendica.com.";
