<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Post to GNU Social'] = 'Invia a GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = "Contatta l'amministratore del sito.<br/>L'URL delle API fornito non è valido.";
$a->strings['We could not contact the GNU Social API with the Path you entered.'] = 'Non possiamo conttattare le API di GNU Social con il percorso che hai inserito.';
$a->strings['GNU Social settings updated.'] = 'Impostazioni di GNU Social aggiornate.';
$a->strings['GNU Social Import/Export/Mirror'] = 'Esporta/Importa/Clona GNU Social';
$a->strings['Globally Available GNU Social OAuthKeys'] = 'OAuthKeys globali di GNU Social';
$a->strings['There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below).'] = "Esistono coppie di chiavi OAuth precofigurate per alcuni server GNU Social. Se usi uno di questi server, per favore scegli queste credenziali. Altrimenti sei libero di collegarti a un'altra installazione di GNU Social (vedi sotto).";
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Provide your own OAuth Credentials'] = 'Fornisci le tue credenziali OAuth';
$a->strings['No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation.'] = "Nessuna coppia di chiavi consumer trovate per GNU Social. Registra il tuo account Friendica come un client desktop nel tuo account GNU Social, copia la coppia di chiavi consumer qui e inserisci l'url base delle API.<br/>Prima di registrare la tua coppia di chiavi OAuth, chiedi all'amministratore se esiste già una coppia di chiavi per questo sito Friendica presso la tua installazione GNU Social preferita.";
$a->strings['OAuth Consumer Key'] = 'OAuth Consumer Key';
$a->strings['OAuth Consumer Secret'] = 'OAuth Consumer Secret';
$a->strings['Base API Path (remember the trailing /)'] = 'Indirizzo di base per le API (ricorda la / alla fine)';
$a->strings['To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.'] = 'Per collegare il tuo account GNU Social, clicca sul bottone per ottenere un codice di sicurezza da GNU Social, che dovrai copiare nel box sottostante e poi inviare la form. Solo i tuoi messaggi <strong>pubblici</strong> saranno inviati a GNU Social.';
$a->strings['Log in with GNU Social'] = 'Accedi con GNU Social';
$a->strings['Copy the security code from GNU Social here'] = 'Copia il codice di sicurezza da GNU Social qui';
$a->strings['Cancel Connection Process'] = 'Annulla il processo di connessione';
$a->strings['Current GNU Social API is'] = 'Le API GNU Social correnti sono';
$a->strings['Cancel GNU Social Connection'] = 'Annulla la connessione a GNU Social';
$a->strings['Currently connected to: '] = 'Al momento connesso con:';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = "Se abilitato tutti i tuoi messaggi <strong>pubblici</strong> possono essere inviati all'account GNU Social associato. Puoi scegliere di farlo sempre (qui) o ogni volta che invii, nelle impostazioni di privacy del messaggio.";
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = "<strong>Nota</strong>: A causa delle tue impostazioni di privacy(<em>Nascondi i dettagli del tuo profilo ai visitatori sconosciuti?</em>) il link potenzialmente incluse nei messaggi pubblici inviati a GNU Social porterà i visitatori a una pagina bianca con una nota che li informa che l'accesso al tuo profilo è stato limitato.";
$a->strings['Allow posting to GNU Social'] = "Permetti l'invio a GNU Social";
$a->strings['Send public postings to GNU Social by default'] = 'Invia sempre i messaggi pubblici a GNU Social';
$a->strings['Mirror all posts from GNU Social that are no replies or repeated messages'] = 'Clona tutti i messaggi da GNU Social che non sono risposte o messaggi ripetuti';
$a->strings['Import the remote timeline'] = 'Importa la timeline remota';
$a->strings['Disabled'] = 'Disabilitato';
$a->strings['Full Timeline'] = 'Timeline completa';
$a->strings['Only Mentions'] = 'Solo menzioni';
$a->strings['Clear OAuth configuration'] = 'Rimuovi la configurazione OAuth';
$a->strings['Site name'] = 'Nome del sito';
$a->strings['Consumer Secret'] = 'Consumer Secret';
$a->strings['Consumer Key'] = 'Consumer Key';
