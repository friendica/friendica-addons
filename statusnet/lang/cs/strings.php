<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Post to GNU Social"] = "Poslat na GNU social";
$a->strings["Please contact your site administrator.<br />The provided API URL is not valid."] = "Obraťte se na administratora webu.<br />Poskytnutý odkaz na API není platný.";
$a->strings["We could not contact the GNU Social API with the Path you entered."] = "Nemohli jsme kontaktovat API GNU social pomocí cesty, kterou jste zadal/a.";
$a->strings["GNU Social settings updated."] = "Nastavení pro GNU social aktualizována.";
$a->strings["GNU Social Import/Export/Mirror"] = "Import/export/zrcadlení GNU social";
$a->strings["Globally Available GNU Social OAuthKeys"] = "Globálně dostupné OAuth klíče pro GNU";
$a->strings["There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below)."] = "Jsou dostupné předkonfigurované páry klíčů OAuth pro některé servery GNU social. Pokud některý z nich používáte, prosím používejte tyto kredenciály. Pokud ne, můžete se klidně připojit k jakékoliv jiné instanci GNU social (viz dole).";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Provide your own OAuth Credentials"] = "Uveďte své vlastní OAuth přihlašovací údaje";
$a->strings["No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation."] = "Nenalezen žádný pár klíčů pro GNU social. Registrujte svůj účet Friendica na Vašem účtu GNU social jako desktopový klient, zkopírujte sem pár klíčů consumer key a zadejte kořenovou složku API.<br />Dříve, než si zaregistrujete Váš vlastní pár klíčů, se zeptejte administrátora, jestli již na Vaší oblíbené instalaci GNU social existuje pár klíčů pro tuto instalaci Friendica.";
$a->strings["OAuth Consumer Key"] = "OAuth Consumer Key";
$a->strings["OAuth Consumer Secret"] = "OAuth Consumer Secret";
$a->strings["Base API Path (remember the trailing /)"] = "Cesta k Base API  (nezapomeňte na koncové /)";
$a->strings["To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social."] = "Pro připojení k Vašemu účtu na GNU social klikněte na tlačítko níže. Obdržíte bezpečnostní kód od GNU social, ten musíte zkopírovat do vyplňovacího pole níže a odeslat formulář. Pouze Vaše <strong>veřejné</strong> příspěvky budou odesílány na GNU social.";
$a->strings["Log in with GNU Social"] = "Přihlásit se pomocí GNU social";
$a->strings["Copy the security code from GNU Social here"] = "Zde překopírujte váš bezpečnostní kód z GNU social";
$a->strings["Cancel Connection Process"] = "Zrušit připojování";
$a->strings["Current GNU Social API is"] = "Aktuální API GNU social je:";
$a->strings["Cancel GNU Social Connection"] = "Zrušit spojení s GNU social";
$a->strings["Currently connected to: "] = "Aktuálně jste připojen/a k:";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Je-li povoleno, všechny Vaše <strong>veřejné</strong> příspěvky mohou být zasílány na související účet na GNU social. Můžete si vybrat, zda-li toto bude výchozí nastavení (zde), nebo budete mít možnost si vybrat požadované chování při psaní každého příspěvku.";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>Poznámka</strong>: Kvůli vašim nastavením o soukromí (<em>Skrýt Vaše profilové detaily před neznámými návštěvníky?</em>), odkaz potenciálně obsažen ve veřejných příspěvcích přeposílaných na GNU social zavedou návštěvníky na prázdnou stránku informující návštěvníky, že přístup na Váš profil byl zakázán.";
$a->strings["Allow posting to GNU Social"] = "Povolit posílání příspěvků na GNU social";
$a->strings["Send public postings to GNU Social by default"] = "Ve výchozím stavu posílat veřejné příspěvky na GNU social";
$a->strings["Mirror all posts from GNU Social that are no replies or repeated messages"] = "Zrcadlit všechny příspěvky z GNU social, které nejsou odpovědi nebo zopakované zprávy";
$a->strings["Import the remote timeline"] = "Importovat vzdálenou časovou osu";
$a->strings["Disabled"] = "Zakázáno";
$a->strings["Full Timeline"] = "Plná časová osa";
$a->strings["Only Mentions"] = "Pouze zmínky";
$a->strings["Clear OAuth configuration"] = "Vymazat konfiguraci OAuth";
$a->strings["Site name"] = "Název webu";
$a->strings["Consumer Secret"] = "Consumer Secret";
$a->strings["Consumer Key"] = "Consumer Key";
