<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Post to StatusNet'] = 'Postați pe StatusNet';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = 'Vă rugăm să vă contactați administratorul de site. <br />URL-ul API furnizat, nu este valid.';
$a->strings['We could not contact the StatusNet API with the Path you entered.'] = 'Nu am putut conecta API pentru StatusNet la Calea pe care ați introdus-o.';
$a->strings['StatusNet settings updated.'] = 'Configurările StatusNet au fost actualizate.';
$a->strings['StatusNet Import/Export/Mirror'] = 'Import/Export/Clonare StatusNet';
$a->strings['Globally Available StatusNet OAuthKeys'] = 'Cheile OAuthKeys StatusNet Disponibile Global';
$a->strings['There are preconfigured OAuth key pairs for some StatusNet servers available. If you are useing one of them, please use these credentials. If not feel free to connect to any other StatusNet instance (see below).'] = 'Acolo sunt preconfigurate perechile de chei OAuthKeys pentru anumite servere StatusNet disponibile. Dacă folosiți una dintre ele, vă rugăm să utilizați aceste acreditive. Dacă nu, vă puteți conecta la orice altă instanță StatusNet (vedeți mai jos).';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['Provide your own OAuth Credentials'] = 'Furnizați propriile dvs. Acreditive OAuth';
$a->strings['No consumer key pair for StatusNet found. Register your Friendica Account as an desktop client on your StatusNet account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited StatusNet installation.'] = 'Nici o pereche de chei de utilizator pentru StatusNet, nu fost găsită. Înregistrați-vă Contul Friendica, ca și client  desktop, pe contul dvs. StatusNet, copiați aici pereche de chei de utilizator, şi introduceți rădăcina-bazei API. <br />Înainte să vă înregistrați propria pereche de chei OAuth, întrebați administratorul dacă nu există deja o pereche de chei, pentru această instalare Friendica, conectată la instalarea dvs. favorită StatusNet.';
$a->strings['OAuth Consumer Key'] = 'Cheia Utilizatorului OAuth';
$a->strings['OAuth Consumer Secret'] = 'Cheia Secretă a Utilizatorului OAuth';
$a->strings['Base API Path (remember the trailing /)'] = 'Cale-Bază API (nu uitați de slash /)';
$a->strings['StatusNet application name'] = 'Numele Aplicației StatusNet';
$a->strings['To connect to your StatusNet account click the button below to get a security code from StatusNet which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to StatusNet.'] = 'Pentru a vă conecta la contul dvs. StatusNet apăsați pe butonul de mai jos pentru a obține un cod de securitate de la StatusNet, pe care va trebui să îl copiați în caseta de introducere mai jos şi trimiteți formularul. Numai postările dvs.<strong>publice</strong> vor fi postate pe StatusNet.';
$a->strings['Log in with StatusNet'] = 'Autentificare cu StatusNet';
$a->strings['Copy the security code from StatusNet here'] = 'Copiați aici codul de securitate din StatusNet';
$a->strings['Cancel Connection Process'] = 'Anulare Proces de Conectare';
$a->strings['Current StatusNet API is'] = 'Cheia API StatusNet Curentă este';
$a->strings['Cancel StatusNet Connection'] = 'Anulare Conectare StatusNet';
$a->strings['Currently connected to: '] = 'Conectat curent la:';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated StatusNet account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Dacă activați, toate postările dvs. <strong>publice</strong> pot fi publicate în contul StatusNet asociat. Puteți face acest lucru, implicit (aici), sau pentru fiecare postare separată, prin opțiunile de postare atunci când compuneți un mesaj.';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to StatusNet will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Notă</strong>: Datorită configurărilor de confidenţialitate (<em>Se ascund detaliile profilului dvs. de vizitatorii necunoscuți?</em>), legătura potențial inclusă în postările publice transmise către StatusNet, va conduce vizitatorul la o pagină goală, informându-l pe vizitator că accesul la profilul dvs. a fost restricţionat.';
$a->strings['Allow posting to StatusNet'] = 'Permite postarea pe StatusNet';
$a->strings['Send public postings to StatusNet by default'] = 'Trimite postările publice pe StatusNet, ca și implicit';
$a->strings['Mirror all posts from statusnet that are no replies or repeated messages'] = 'Clonează toate postările, din StatusNet, care nu sunt răspunsuri sau mesaje repetate';
$a->strings['Import the remote timeline'] = 'Importare cronologie la distanță';
$a->strings['Clear OAuth configuration'] = 'Ștergeți configurările OAuth ';
$a->strings['Site name'] = 'Numele saitului';
$a->strings['Consumer Secret'] = 'Cheia Secretă a Utilizatorului';
$a->strings['Consumer Key'] = 'Cheia Utilizatorului';
$a->strings['Application name'] = 'Numele aplicației';
