<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Twitter'] = 'Publier sur Twitter';
$a->strings['You submitted an empty PIN, please Sign In with Twitter again to get a new one.'] = 'Vous avez envoyé un PIN vide, veuillez vous connecter à Twitter à nouveau pour en avoir un autre.';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'Aucune clé d\'application pour Twitter n\'a été trouvée. Merci de contacter l\'administrateur de votre site.';
$a->strings['At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'Sur cette instance de Friendica, le connecteur Twitter a été activé, mais vous n\'avez pas encore connecté votre compte local à votre compte Twitter. Pour ce faire, cliquer sur le bouton ci-dessous. Vous obtiendrez alors un \'PIN\' de Twitter, que vous devrez copier dans le champ ci-dessous, puis soumettre le formulaire. Seules vos publications <strong>publiques</strong> seront transmises à Twitter.';
$a->strings['Log in with Twitter'] = 'Se connecter avec Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Copier le PIN de Twitter ici';
$a->strings['An error occured: '] = 'Une erreur est survenue :';
$a->strings['Currently connected to: <a href="https://twitter.com/%1$s" target="_twitter">%1$s</a>'] = 'Actuellement connecté à : <a href="https://twitter.com/%1$s" target="_twitter">%1$s</a>';
$a->strings['<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Note</strong>: Du fait de vos paramètres de vie privée (<em>Cacher les détails de votre profil des visiteurs inconnus?</em>), le lien potentiellement inclus dans les publications publiques relayées vers Twitter conduira les visiteurs vers une page blanche les informant que leur accès à votre profil a été restreint.';
$a->strings['Invalid Twitter info'] = 'Informations Twitter invalides';
$a->strings['Disconnect'] = 'Se déconnecter';
$a->strings['Allow posting to Twitter'] = 'Autoriser la publication sur Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'En cas d\'activation, toutes vos publications <strong>publiques</strong> seront transmises au compte Twitter associé. Vous pourrez choisir de le faire par défaut (ici), ou bien pour chaque publication séparément lors de sa rédaction.';
$a->strings['Send public postings to Twitter by default'] = 'Envoyer par défaut les publications publiques sur Twitter';
$a->strings['Use threads instead of truncating the content'] = 'Utiliser des fils de discussion (threads) au lieu de tronquer le contenu';
$a->strings['Mirror all posts from twitter that are no replies'] = 'Synchroniser toutes les publications de Twitter qui ne sont pas des réponses';
$a->strings['Import the remote timeline'] = 'Importer la Timeline distante';
$a->strings['Automatically create contacts'] = 'Créer automatiquement les contacts';
$a->strings['This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here.'] = 'Cela va automatiquement créer un contact dans Friendica dès qu\'une publication d\'un contact existant est reçue de Twitter. Si vous n\'activez pas ceci, vous devrez ajouter manuellement ces contacts dans Friendica afin d\'y voir leurs publications.';
$a->strings['Follow in fediverse'] = 'Suivre dans le fediverse';
$a->strings['Automatically subscribe to the contact in the fediverse, when a fediverse account is mentioned in name or description and we are following the Twitter contact.'] = 'Suivre automatiquement le contact dans le fediverse, quand un compte fediverse est mentionné dans le nom ou la description et que le contact Twitter est suivi.';
$a->strings['Twitter Import/Export/Mirror'] = 'Importation/Exportation/Synchronisation avec Twitter';
$a->strings['Please connect a Twitter account in your Social Network settings to import Twitter posts.'] = 'Merci de connecter un compte Twitter depuis vos Paramètres de réseaux sociaux afin d\'importer les publications Twitter.';
$a->strings['Twitter post not found.'] = 'Publication Twitter non trouvée.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Consumer key'] = 'Consumer key';
$a->strings['Consumer secret'] = 'Consumer secret';
$a->strings['%s on Twitter'] = '%s sur Twitter';
