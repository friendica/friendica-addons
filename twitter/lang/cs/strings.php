<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Post to Twitter'] = 'Poslat příspěvek na Twitter';
$a->strings['Twitter settings updated.'] = 'Nastavení Twitteru aktualizováno.';
$a->strings['Twitter Import/Export/Mirror'] = 'Twitter Import/Export/Zrcadlení';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'Nenalezen žádný spotřebitelský páru klíčů pro Twitter. Obraťte se na administrátora webu.';
$a->strings['At this Friendica instance the Twitter plugin was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'Na této Friendica instanci je sice povolen Twitter plugin, ale vy jste si ještě nenastavili svůj Twitter účet. Svůj účet si můžete nastavit kliknutím na tlačítko níže k získání PINu z Vašeho Twitteru, který si zkopírujte do níže uvedeného vstupního pole a odešlete formulář. Pouze vaše <strong>veřejné</strong> příspěvky budou zaslány na Twitter.';
$a->strings['Log in with Twitter'] = 'Přihlásit se s Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Zkopírujte sem PIN z Twitteru';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['Currently connected to: '] = 'V současné době připojen k:';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Je-li povoleno, všechny Vaše <strong>veřejné</strong> příspěvky mohou být zaslány na související Twitter účet. Můžete si vybrat, zda-li toto bude výchozí nastavení (zde), nebo budete mít možnost si vybrat požadované chování při psaní každého příspěvku.';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = "<strong>Upozornění</strong>: Z důvodů Vašeho nastavení ochrany soukromí (<em> Skrýt Vaše profilové detaily před neznámými čtenáři?</em>) \nodkaz potenciálně zahrnutý ve Vašich veřejných příspěvcích poslaných do sítě Twitter přesměruje návštěvníky na prázdnou stránku informující návštěvníky, že přístup k vašemu profilu je omezen.";
$a->strings['Allow posting to Twitter'] = 'Povolit odesílání na Twitter';
$a->strings['Send public postings to Twitter by default'] = 'Defaultně zasílat veřejné komentáře na Twitter';
$a->strings['Mirror all posts from twitter that are no replies'] = 'Zrcadlit všechny příspěvky z twitteru, které nejsou odpověďmi';
$a->strings['Import the remote timeline'] = 'Importovat vzdálenou časovou osu';
$a->strings['Automatically create contacts'] = 'Automaticky vytvářet kontakty';
$a->strings['Clear OAuth configuration'] = 'Vymazat konfiguraci OAuth';
$a->strings['Twitter post failed. Queued for retry.'] = 'Zaslání příspěvku na Twitter selhalo. Příspěvek byl zařazen do fronty pro opakované odeslání.';
$a->strings['Settings updated.'] = 'Nastavení aktualizováno.';
$a->strings['Consumer key'] = 'Consumer key';
$a->strings['Consumer secret'] = 'Consumer secret';
$a->strings['Name of the Twitter Application'] = 'Název aplikace Twitter';
$a->strings['set this to avoid mirroring postings from ~friendica back to ~friendica'] = 'použijte toto pro zabránění zrcadlení příspvků z Friedica zpět do Friendica';
