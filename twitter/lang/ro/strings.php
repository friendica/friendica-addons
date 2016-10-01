<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Post to Twitter'] = 'Postați pe Twitter';
$a->strings['Twitter settings updated.'] = 'Configurările Twitter au fost actualizate.';
$a->strings['Twitter Import/Export/Mirror'] = 'Import/Export/Clonare Twitter';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'Nici o pereche de chei de utilizator pentru Twitter nu a fost găsită. Vă rugăm să vă contactați administratorul de site.';
$a->strings['At this Friendica instance the Twitter plugin was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'Pe această sesiune Friendica, modulul Twitter era activat, dar încă nu v-ați conectat contul la profilul dvs. Twitter. Pentru aceasta apăsați pe butonul de mai jos pentru a obține un PIN de pe Twitter pe care va trebui să îl copiați în caseta de introducere mai jos şi trimiteți formularul. Numai postările dumneavoastră <strong>publice</strong> vor fi postate pe Twitter.';
$a->strings['Log in with Twitter'] = 'Autentificare prin Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Copiați aici PIN-ul de la Twitter';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['Currently connected to: '] = 'Conectat curent la:';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Dacă activați, toate postările dvs. <strong>publice</strong> pot fi publicate în contul Twitter asociat. Puteți face acest lucru, implicit (aici), sau pentru fiecare postare separată, prin opțiunile de postare atunci când compuneți un mesaj.';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Notă</strong>: Datorită configurărilor de confidenţialitate (<em>Se ascund detaliile profilului dvs. de vizitatorii necunoscuți?</em>), legătura potențial inclusă în postările publice transmise către Twitter, va conduce vizitatorul la o pagină goală, informându-l pe vizitator că accesul la profilul dvs. a fost restricţionat.';
$a->strings['Allow posting to Twitter'] = 'Permite postarea pe Twitter';
$a->strings['Send public postings to Twitter by default'] = 'Trimite postările publice pe Twitter, ca și implicit';
$a->strings['Mirror all posts from twitter that are no replies'] = 'Clonează toate postările, din Twitter, care nu sunt răspunsuri';
$a->strings['Import the remote timeline'] = 'Importare cronologie la distanță';
$a->strings['Automatically create contacts'] = 'Creați Automat contactele';
$a->strings['Clear OAuth configuration'] = 'Ștergeți configurările OAuth';
$a->strings['Twitter post failed. Queued for retry.'] = 'Postarea pe Twitter a eșuat. S-a pus în așteptare pentru reîncercare.';
$a->strings['Settings updated.'] = 'Configurări actualizate.';
$a->strings['Consumer key'] = 'Cheia Utilizatorului';
$a->strings['Consumer secret'] = 'Cheia Secretă a Utilizatorului';
$a->strings['Name of the Twitter Application'] = 'Numele Aplicației Twitter';
$a->strings['set this to avoid mirroring postings from ~friendica back to ~friendica'] = 'stabiliți aceasta pentru a evita clonarea postărilor din ~friendica înapoi la ~friendica';
