<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to GNU Social"] = "Lähetä GNU Socialiin";
$a->strings["Please contact your site administrator.<br />The provided API URL is not valid."] = "Ota yhteyttä sivuston ylläpitäjään.<br />API URL-osoite on virheellinen.";
$a->strings["We could not contact the GNU Social API with the Path you entered."] = "";
$a->strings["GNU Social settings updated."] = "GNU Social -asetukset päivitetty.";
$a->strings["GNU Social Import/Export/Mirror"] = "GNU social tuonti/vienti/peili";
$a->strings["Globally Available GNU Social OAuthKeys"] = "";
$a->strings["There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below)."] = "";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Provide your own OAuth Credentials"] = "";
$a->strings["No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation."] = "";
$a->strings["OAuth Consumer Key"] = "OAuth kuluttajan avain";
$a->strings["OAuth Consumer Secret"] = "OAuth kuluttajasalaisuus";
$a->strings["Base API Path (remember the trailing /)"] = "";
$a->strings["To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social."] = "";
$a->strings["Log in with GNU Social"] = "Kirjaudu sisään GNU socialilla";
$a->strings["Copy the security code from GNU Social here"] = "";
$a->strings["Cancel Connection Process"] = "";
$a->strings["Current GNU Social API is"] = "";
$a->strings["Cancel GNU Social Connection"] = "";
$a->strings["Currently connected to: "] = "";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "";
$a->strings["<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "";
$a->strings["Allow posting to GNU Social"] = "Salli julkaisut GNU socialiin";
$a->strings["Send public postings to GNU Social by default"] = "Lähetä oletuksena kaikki julkiset julkaisut GNU socialiin";
$a->strings["Mirror all posts from GNU Social that are no replies or repeated messages"] = "Peilaa kaikki julkaisut GNU socialista jotka eivät ole vastauksia tai toistettuja viestejä";
$a->strings["Import the remote timeline"] = "Tuo etäaikajana";
$a->strings["Disabled"] = "Poistettu käytöstä";
$a->strings["Full Timeline"] = "Koko aikajana";
$a->strings["Only Mentions"] = "";
$a->strings["Clear OAuth configuration"] = "";
$a->strings["Site name"] = "Sivuston nimi";
$a->strings["Consumer Secret"] = "Kuluttajasalaisuus";
$a->strings["Consumer Key"] = "Kuluttajan avain";
