<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to GNU Social"] = "Beküldés a GNU Socialra";
$a->strings["Please contact your site administrator.<br />The provided API URL is not valid."] = "Vegye fel a kapcsolatot az oldal adminisztrátorával.<br />A megadott API URL nem érvényes.";
$a->strings["We could not contact the GNU Social API with the Path you entered."] = "Nem tudtunk kapcsolatba lépni a GNU Social API-val azon az útvonalon, amelyet megadott.";
$a->strings["GNU Social Import/Export/Mirror"] = "GNU Social importálás, exportálás vagy tükrözés";
$a->strings["Globally Available GNU Social OAuthKeys"] = "Globálisan elérhető GNU Social OAuth-kulcsok";
$a->strings["There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below)."] = "Előre beállított OAuth-kulcspárok érhetők el néhány GNU Social kiszolgálóhoz. Ha ezek egyikét használja, akkor használja ezeket a hitelesítési adatokat. Ha nem használja, akkor nyugodtan kapcsolódjon bármely egyéb GNU Social példányhoz (lásd lent).";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Provide your own OAuth Credentials"] = "Adja meg a saját OAuth hitelesítési adatait";
$a->strings["No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation."] = "Nem találhatók felhasználói kulcspárok a GNU Socialhoz. Regisztrálja a Friendica fiókját asztali kliensként a GNU Social fiókjánál, másolja be a felhasználói kulcspárt ide, és adja meg az API alapgyökerét.<br />Mielőtt saját OAuth kulcspárt regisztrálna, kérdezze meg az adminisztrátort, hogy van-e már kulcspár ehhez a Friendica telepítéshez a kedvenc GNU Social telepítésénél.";
$a->strings["OAuth Consumer Key"] = "OAuth felhasználói kulcs";
$a->strings["OAuth Consumer Secret"] = "OAuth felhasználói titok";
$a->strings["Base API Path (remember the trailing /)"] = "Alap API útvonal (ne felejtse el a záró / karaktert)";
$a->strings["To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social."] = "A GNU Social fiókhoz való kapcsolódáshoz kattintson a lenti gombra, hogy megkapja a biztonsági kódot a GNU Socialtól, amelyet a lenti beviteli mezőbe kell bemásolnia, majd el kell küldenie az űrlapot. Csak a <strong>nyilvános</strong> bejegyzései lesznek beküldve a GNU Socialra.";
$a->strings["Log in with GNU Social"] = "Bejelentkezés GNU Social használatával";
$a->strings["Copy the security code from GNU Social here"] = "Másolja be ide a GNU Socialtól származó biztonsági kódot";
$a->strings["Cancel Connection Process"] = "Kapcsolódási folyamat megszakítása";
$a->strings["Current GNU Social API is"] = "A jelenlegi GNU Social API";
$a->strings["Cancel GNU Social Connection"] = "GNU Social kapcsolódás megszakítása";
$a->strings["Currently connected to: "] = "Jelenleg ehhez van kapcsolódva: ";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Ha engedélyezve van, akkor az összes <strong>nyilvános</strong> beküldés beküldhető a hozzárendelt GNU Social fiókba. Kiválaszthatja, hogy ezt alapértelmezetten szeretné-e (itt), vagy minden egyes beküldésnél különállóan a beküldési beállításokban, amikor megírja a bejegyzést.";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>Megjegyzés</strong>: az adatvédelmi beállításai miatt (<em>Elrejti a profilja részleteit az ismeretlen megtekintők elől?</em>) a GNU Socialra továbbított nyilvános beküldésekben vélhetően tartalmazott hivatkozás a látogatót egy üres oldalra fogja vezetni, amely arról tájékoztatja a látogatót, hogy a profiljához való hozzáférés korlátozva lett.";
$a->strings["Allow posting to GNU Social"] = "Beküldés engedélyezése a GNU Socialra";
$a->strings["Send public postings to GNU Social by default"] = "Nyilvános beküldések küldése a GNU Socialra alapértelmezetten";
$a->strings["Mirror all posts from GNU Social that are no replies or repeated messages"] = "A GNU Socialtól származó összes bejegyzés tükrözése, amelyek nem válaszok vagy ismételt üzenetek";
$a->strings["Import the remote timeline"] = "A távoli idővonal importálása";
$a->strings["Disabled"] = "Letiltva";
$a->strings["Full Timeline"] = "Teljes idővonal";
$a->strings["Only Mentions"] = "Csak említések";
$a->strings["Clear OAuth configuration"] = "OAuth beállítás törlése";
$a->strings["Site name"] = "Oldal neve";
$a->strings["Consumer Secret"] = "Felhasználói titok";
$a->strings["Consumer Key"] = "Felhasználói kulcs";
