<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return ($n % 10 != 1 || $n % 100 == 11);;
}}
;
$a->strings["Post to GNU Social"] = "Senda á GNU Social";
$a->strings["Please contact your site administrator.<br />The provided API URL is not valid."] = "Hafðu samband við kerfisstjóra.<br />Uppgefin API-slóð er ógild.";
$a->strings["We could not contact the GNU Social API with the Path you entered."] = "Ekki náðist í GNU Social API með slóðinni sem þú gafst upp.";
$a->strings["GNU Social settings updated."] = "Stillingar GNU Social uppfærðar.";
$a->strings["GNU Social Import/Export/Mirror"] = "";
$a->strings["Globally Available GNU Social OAuthKeys"] = "Víðværir OAuth-lyklar GNU Social eru til taks";
$a->strings["There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below)."] = "Það eru forstillt OAuth-lyklapör í sumum GNU Social þjónum.  Ef þú ert að nota slíkt par, notaðu þá þau auðkenni. Ef ekki þá er þér frjálst að tengjast hvaða öðrum GNU Social þjónum (sjá fyrir neðan).";
$a->strings["Save Settings"] = "Vista stillingar";
$a->strings["Provide your own OAuth Credentials"] = "Gefðu upp eigin OAuth auðkenni";
$a->strings["No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation."] = "";
$a->strings["OAuth Consumer Key"] = "OAuth-lykill notanda";
$a->strings["OAuth Consumer Secret"] = "OAuth-leyniorð notanda";
$a->strings["Base API Path (remember the trailing /)"] = "Grunn API-slóð (muna eftir / í endann)";
$a->strings["To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social."] = "Til að tengjast GNU Social notandaaðgangnum ýttu á hnappinn hér fyrir neðan, þá fæst öryggislykill frá GNU Social sem þarf að afrita í svæðið fyrir neðan og senda inn. Aðeins <strong>opinberar</strong> færslur munu flæða yfir á GNU Social.";
$a->strings["Log in with GNU Social"] = "Skrá inn með GNU Social";
$a->strings["Copy the security code from GNU Social here"] = "Afrita öryggislykil frá GNU Social hingað";
$a->strings["Cancel Connection Process"] = "Hætta við tengiferli";
$a->strings["Current GNU Social API is"] = "Núverandi GNU Social API er";
$a->strings["Cancel GNU Social Connection"] = "Hætta við GNU Social tengingu";
$a->strings["Currently connected to: "] = "Núna tengdur við:";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "Ef virkt þá geta allar <strong>opinberu</strong> stöðu meldingarnar þínar verið birtar á tengdri GNU Social síðu. Þú getur valið að gera þetta sjálfvirkt (hér) eða fyrir hvern póst í senn þegar hann er skrifaður.";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "";
$a->strings["Allow posting to GNU Social"] = "Leyfa sendingu færslna til GNU Social";
$a->strings["Send public postings to GNU Social by default"] = "Sjálfgefið senda opinberar færslur á GNU Social";
$a->strings["Mirror all posts from GNU Social that are no replies or repeated messages"] = "";
$a->strings["Import the remote timeline"] = "Flytja inn fjartengdu tímalínuna";
$a->strings["Disabled"] = "Slökkt";
$a->strings["Full Timeline"] = "Öll tímalínan";
$a->strings["Only Mentions"] = "";
$a->strings["Clear OAuth configuration"] = "Hreinsa OAuth stillingar";
$a->strings["Site name"] = "Heiti vefsvæðis";
$a->strings["Consumer Secret"] = "Leyniorð notanda";
$a->strings["Consumer Key"] = "Lykill notanda";
