<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to GNU Social'] = 'Invia a GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = 'Contatta l\'amministratore del sito.<br/>L\'URL delle API fornito non è valido.';
$a->strings['We could not contact the GNU Social API with the Path you entered.'] = 'Non possiamo conttattare le API di GNU Social con il percorso che hai inserito.';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Currently connected to: <a href="%s" target="_statusnet">%s</a>'] = 'Attualmente connesso a: <a href="%s" target="_statusnet">%s</a>';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Nota</strong>: A causa delle tue impostazioni di privacy(<em>Nascondi i dettagli del tuo profilo ai visitatori sconosciuti?</em>) il collegamento potenzialmente incluso nei messaggi pubblici inviati a GNU Social porterà i visitatori a una pagina bianca con una nota che li informa che l\'accesso al tuo profilo è stato limitato.';
$a->strings['Clear OAuth configuration'] = 'Rimuovi la configurazione OAuth';
$a->strings['Cancel GNU Social Connection'] = 'Annulla la connessione a GNU Social';
$a->strings['Globally Available GNU Social OAuthKeys'] = 'OAuthKeys globali di GNU Social';
$a->strings['There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below).'] = 'Esistono coppie di chiavi OAuth precofigurate per alcuni server GNU Social. Se usi uno di questi server, per favore scegli queste credenziali. Altrimenti sei libero di collegarti a un\'altra installazione di GNU Social (vedi sotto).';
$a->strings['Provide your own OAuth Credentials'] = 'Fornisci le tue credenziali OAuth';
$a->strings['No consumer key pair for GNU Social found. Register your Friendica Account as a desktop application on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorite GNU Social installation.'] = 'Coppia di chiavi consumer per GNU Social non trovata. Registra il tuo Account Friendica come applicazione desktop nel tuo account GNU Social, copia la coppia di chiavi consumer qui e inserisci il percorso delle API.<br />Prima di registrare una tua coppia di chiavi OAuth chiedi all\'amministratore se esiste già una coppia di chiavi tra questa installazione Friendica e l\'installazione GNU Social.';
$a->strings['To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.'] = 'Per collegare il tuo account GNU Social, clicca sul bottone per ottenere un codice di sicurezza da GNU Social, che dovrai copiare nel box sottostante e poi inviare la form. Solo i tuoi messaggi <strong>pubblici</strong> saranno inviati a GNU Social.';
$a->strings['Log in with GNU Social'] = 'Accedi con GNU Social';
$a->strings['Cancel Connection Process'] = 'Annulla il processo di connessione';
$a->strings['Current GNU Social API is: %s'] = 'La API attuale di GNU Social è: %s';
$a->strings['OAuth Consumer Key'] = 'OAuth Consumer Key';
$a->strings['OAuth Consumer Secret'] = 'OAuth Consumer Secret';
$a->strings['Base API Path (remember the trailing /)'] = 'Indirizzo di base per le API (ricorda la / alla fine)';
$a->strings['Copy the security code from GNU Social here'] = 'Copia il codice di sicurezza da GNU Social qui';
$a->strings['Allow posting to GNU Social'] = 'Permetti l\'invio a GNU Social';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Se abilitato tutti i tuoi messaggi <strong>pubblici</strong> possono essere inviati all\'account GNU Social associato. Puoi scegliere di farlo sempre (qui) o ogni volta che invii, nelle impostazioni di privacy del messaggio.';
$a->strings['Post to GNU Social by default'] = 'Pubblica su GNU Social per impostazione predefinita';
$a->strings['GNU Social Import/Export/Mirror'] = 'Esporta/Importa/Clona GNU Social';
$a->strings['Site name'] = 'Nome del sito';
$a->strings['Consumer Secret'] = 'Consumer Secret';
$a->strings['Consumer Key'] = 'Consumer Key';
