<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Post to StatusNet"] = "Poslat příspěvek na StatusNet";
$a->strings["Please contact your site administrator.<br />The provided API URL is not valid."] = "Obraťte se na administratora webu.<br />Poskytnutý odkaz na API není platný.";
$a->strings["We could not contact the StatusNet API with the Path you entered."] = "S cestou, kterou jste zadali, se nebylo možné spojit s API StatusNetu.";
$a->strings["StatusNet settings updated."] = "Nastavení StatusNetu aktualizováno.";
$a->strings["StatusNet Import/Export/Mirror"] = "StatusNet Import/Export/Zrcadlení";
$a->strings["Globally Available StatusNet OAuthKeys"] = "Globálně dostupné StatusNet OAuth klíče";
$a->strings["There are preconfigured OAuth key pairs for some StatusNet servers available. If you are useing one of them, please use these credentials. If not feel free to connect to any other StatusNet instance (see below)."] = "Jsou dostupné přednastavené OAuth páry klíčů pro některé servery StatusNetu. Pokud používáte některý z nich, použijte toto přihlášení. Pokud ne, neváhejte se připojit k jiné instanci StatusNet (viz níže).";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Provide your own OAuth Credentials"] = "Uveďte své vlastní OAuth přihlašovací údaje";
$a->strings["No consumer key pair for StatusNet found. Register your Friendica Account as an desktop client on your StatusNet account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited StatusNet installation."] = "Nenalezen žádný spotřebitelský páru klíčů pro StatusNet. Zaregistrujte si svůj účet Friendica jako desktopový klient ve Vašem účtu StatusNet, zkopírujte si sem spotřebitelský páru klíčů a vložte API base root.<br />Předtím, než si zaregistrujete Váš vlastní pár klíčů OAuth, zjistěte si od Friendica administrátora, zda-li již existuje pár klíčů pro tuto instalaci Friendica pro Vaši oblíbenou StatusNet instalaci.";
$a->strings["OAuth Consumer Key"] = "OAuth Consumer Key";
$a->strings["OAuth Consumer Secret"] = "OAuth Consumer Secret";
$a->strings["Base API Path (remember the trailing /)"] = "Cesta k Base API  (nezapomeňte na koncové /)";
$a->strings["StatusNet application name"] = "StatusNet název aplikace";
$a->strings["To connect to your StatusNet account click the button below to get a security code from StatusNet which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to StatusNet."] = "Chcete-li připojit k vašemu účtu StatusNet klikněte na tlačítko níže, abyste dostati bezpečnostní kód ze StatusNetu, který musíte zkopírovat do vstupního pole níže a odelat formulář. Pouze Vaše <strong>veřejné</strong> příspěvky budou zveřejněny na StatusNetu.";
$a->strings["Log in with StatusNet"] = "Přihlásit se s StatusNet";
$a->strings["Copy the security code from StatusNet here"] = "Zkopírujte sem bezpečnostní kód ze StatusNet";
$a->strings["Cancel Connection Process"] = "Zrušit připojování";
$a->strings["Current StatusNet API is"] = "Aktuální StatusNet API je";
$a->strings["Cancel StatusNet Connection"] = "Zrušit StatusNet připojení";
$a->strings["Currently connected to: "] = "V současné době připojen k:";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated StatusNet account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Je-li povoleno, všechny Vaše <strong>veřejné</strong> příspěvky mohou být zaslány na související StatusNet účet. Můžete si vybrat, zda-li toto bude výchozí nastavení (zde), nebo budete mít možnost si vybrat požadované chování při psaní každého příspěvku.";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to StatusNet will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>Upozornění</strong>: Z důvodů Vašeho nastavení ochrany soukromí (<em> Skrýt Vaše profilové detaily před neznámými čtenáři?</em>) \nodkaz potenciálně zahrnutý ve Vašich veřejných příspěvcích poslaných do sítě StatusNet přesměruje návštěvníky na prázdnou stránku informující návštěvníky, že přístup k vašemu profilu je omezen.";
$a->strings["Allow posting to StatusNet"] = "Povolit zasílání příspěvků na StatusNet";
$a->strings["Send public postings to StatusNet by default"] = "Standardně poslílat veřejné příspěvky na StatusNet";
$a->strings["Mirror all posts from statusnet that are no replies or repeated messages"] = "Zrcadlit všechny příspěvky ze statusnet, které nejsou odpověďmi nebo opakovanými zprávami";
$a->strings["Import the remote timeline"] = "Importovat vzdálenou časovou osu";
$a->strings["Clear OAuth configuration"] = "Vymazat konfiguraci OAuth";
$a->strings["Site name"] = "Název webu";
$a->strings["Consumer Secret"] = "Consumer Secret";
$a->strings["Consumer Key"] = "Consumer Key";
$a->strings["Application name"] = "Název aplikace";
