<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to Twitter"] = "Beküldés a Twitterre";
$a->strings["You submitted an empty PIN, please Sign In with Twitter again to get a new one."] = "Üres PIN-kódot küldött be. Jelentkezzen be a Twitter használatával újra, hogy egy újat kapjon.";
$a->strings["Twitter Import/Export/Mirror"] = "Twitter importálás, exportálás vagy tükrözés";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Nem találhatók felhasználói kulcspárok a Twitterhez. Vegye fel a kapcsolatot az oldal adminisztrátorával.";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "Ennél a Friendica példánynál a Twitter bővítmény engedélyezve lett, de még nem kapcsolta hozzá a fiókját a Twitter-fiókjához. Ehhez kattintson a lenti gombra, hogy kapjon egy PIN-kódot a Twittertől, amelyet a lenti beviteli mezőbe kell bemásolnia, majd el kell küldenie az űrlapot. Csak a <strong>nyilvános</strong> bejegyzései lesznek beküldve a Twitterre.";
$a->strings["Log in with Twitter"] = "Bejelentkezés Twitter használatával";
$a->strings["Copy the PIN from Twitter here"] = "Másolja be ide a Twittertől kapott PIN-kódot";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["An error occured: "] = "Hiba történt: ";
$a->strings["Currently connected to: "] = "Jelenleg ehhez van kapcsolódva: ";
$a->strings["Disconnect"] = "Leválasztás";
$a->strings["Allow posting to Twitter"] = "Beküldés engedélyezése a Twitterre";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Ha engedélyezve van, akkor az összes <strong>nyilvános</strong> beküldés beküldhető a hozzárendelt Twitter-fiókba. Kiválaszthatja, hogy ezt alapértelmezetten szeretné-e (itt), vagy minden egyes beküldésnél különállóan a beküldési beállításokban, amikor megírja a bejegyzést.";
$a->strings["<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>Megjegyzés</strong>: az adatvédelmi beállításai miatt (<em>Elrejti a profilja részleteit az ismeretlen megtekintők elől?</em>) a Twitterre továbbított nyilvános beküldésekben vélhetően tartalmazott hivatkozás a látogatót egy üres oldalra fogja vezetni, amely arról tájékoztatja a látogatót, hogy a profiljához való hozzáférés korlátozva lett.";
$a->strings["Send public postings to Twitter by default"] = "Nyilvános beküldések küldése a Twitterre alapértelmezetten";
$a->strings["Mirror all posts from twitter that are no replies"] = "A Twittertől származó összes bejegyzés tükrözése, amelyek nem válaszok";
$a->strings["Import the remote timeline"] = "A távoli idővonal importálása";
$a->strings["Automatically create contacts"] = "Partnerek automatikus létrehozása";
$a->strings["This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again."] = "Ez automatikusan létre fog hozni egy partnert a Friendicán, amint üzenetet fogad egy meglévő partnertől a Twitter hálózaton keresztül. Ha ezt nem engedélyezi, akkor kézzel kell hozzáadnia azokat a Twitter-partnereket a Friendicában, akiktől bejegyzéseket szeretne látni itt. Azonban ha engedélyezve van, akkor nem tudja egyszerűen eltávolítani a Twitter-partnert a Friendica partnerlistájából, mivel az újra létrehozza ezt a partnert, amikor ismét beküld valamit.";
$a->strings["Consumer key"] = "Felhasználói kulcs";
$a->strings["Consumer secret"] = "Felhasználói titok";
$a->strings["%s on Twitter"] = "%s a Twitteren";
