<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Invia a Twitter';
$a->strings['You submitted an empty PIN, please Sign In with Twitter again to get a new one.'] = 'Hai inserito un PIN vuoto, autenticati con Twitter nuovamente per averne uno nuovo.';
$a->strings['Twitter Import/Export/Mirror'] = 'Importa/Esporta/Clona Twitter';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'Nessuna coppia di chiavi per Twitter trovata. Contatta l\'amministratore del sito.';
$a->strings['At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'Il componente aggiuntivo Twitter è abilitato ma non hai ancora collegato i tuoi account Friendica e Twitter. Per farlo, clicca il bottone qui sotto per ricevere un PIN da Twitter che dovrai copiare nel campo qui sotto. Solo i tuoi messaggi <strong>pubblici</strong> saranno inviati a Twitter.';
$a->strings['Log in with Twitter'] = 'Accedi con Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Copia il PIN da Twitter qui';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['An error occured: '] = 'Si è verificato un errore:';
$a->strings['Currently connected to: '] = 'Al momento connesso con:';
$a->strings['Disconnect'] = 'Disconnetti';
$a->strings['Allow posting to Twitter'] = 'Permetti l\'invio a Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Se abilitato tutti i tuoi messaggi <strong>pubblici</strong> possono essere inviati all\'account Twitter associato. Puoi scegliere di farlo sempre (qui) o ogni volta che invii, nelle impostazioni di privacy del messaggio.';
$a->strings['<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Nota</strong>: A causa delle tue impostazioni di privacy(<em>Nascondi i dettagli del tuo profilo ai visitatori sconosciuti?</em>) il collegamento potenzialmente incluso nei messaggi pubblici inviati a Twitter porterà i visitatori a una pagina bianca con una nota che li informa che l\'accesso al tuo profilo è stato limitato.';
$a->strings['Send public postings to Twitter by default'] = 'Invia sempre i messaggi pubblici a Twitter';
$a->strings['Mirror all posts from twitter that are no replies'] = 'Clona tutti i messaggi da Twitter che non sono risposte';
$a->strings['Import the remote timeline'] = 'Importa la timeline remota';
$a->strings['Automatically create contacts'] = 'Crea automaticamente i contatti';
$a->strings['This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again.'] = 'Questo creerà automaticamente un contatto in Friendica appena ricevi un messaggio da un tuo contatto sulla rete Twitter. Se non abiliti questa opzione, dovrai aggiungere a mano in Friendica i contatti Twitter da cui vuoi ricevere i messaggi. Se abilitato, però, non potrai semplicemente rimuovere un contatto Twitter dal tuo elenco contatti su Friendica, dato che questo sarà ricreato la prossima volta che invierà un messaggio.';
$a->strings['Consumer key'] = 'Consumer key';
$a->strings['Consumer secret'] = 'Consumer secret';
$a->strings['%s on Twitter'] = '%s su Twitter';
