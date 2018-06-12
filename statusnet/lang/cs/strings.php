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
$a->strings["GNU Social Import/Export/Mirror"] = "";
$a->strings["Globally Available GNU Social OAuthKeys"] = "";
$a->strings["There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below)."] = "";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Provide your own OAuth Credentials"] = "Uveďte své vlastní OAuth přihlašovací údaje";
$a->strings["No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation."] = "";
$a->strings["OAuth Consumer Key"] = "OAuth Consumer Key";
$a->strings["OAuth Consumer Secret"] = "OAuth Consumer Secret";
$a->strings["Base API Path (remember the trailing /)"] = "Cesta k Base API  (nezapomeňte na koncové /)";
$a->strings["To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social."] = "";
$a->strings["Log in with GNU Social"] = "Přihlásit se pomocí GNU social";
$a->strings["Copy the security code from GNU Social here"] = "Zde překopírujte váš bezpečnostní kód z GNU social";
$a->strings["Cancel Connection Process"] = "Zrušit připojování";
$a->strings["Current GNU Social API is"] = "";
$a->strings["Cancel GNU Social Connection"] = "Zrušit spojení s GNU social";
$a->strings["Currently connected to: "] = "Aktuálně jste připojen/a k:";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "";
$a->strings["Allow posting to GNU Social"] = "Povolit posílání příspěvků na GNU social";
$a->strings["Send public postings to GNU Social by default"] = "";
$a->strings["Mirror all posts from GNU Social that are no replies or repeated messages"] = "";
$a->strings["Import the remote timeline"] = "";
$a->strings["Disabled"] = "Zakázáno";
$a->strings["Full Timeline"] = "";
$a->strings["Only Mentions"] = "";
$a->strings["Clear OAuth configuration"] = "Vymazat konfiguraci OAuth";
$a->strings["Site name"] = "Název webu";
$a->strings["Consumer Secret"] = "Consumer Secret";
$a->strings["Consumer Key"] = "Consumer Key";
