<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to GNU Social'] = 'Publier sur GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = 'Merci de contacter l\'administrateur du site.<br />L\'URL d\'API fournie est invalide.';
$a->strings['We could not contact the GNU Social API with the Path you entered.'] = 'Impossible de se connecter à l\'API GNU Social avec le chemin indiqué.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Currently connected to: <a href="%s" target="_statusnet">%s</a>'] = 'Actuellement connecté à : <a href="%s" target="_statusnet">%s</a>';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Note</strong>: En lien avec vos paramètres de confidentialité (<em>Masquer vos détails de profil de visiteurs inconnus ?</em>) le lien potentiellement inclus dans vos publications publiques relayées à GNU Social emmèneront le visiteur à une page blanche l\'informant que l\'accès à votre profil a été restreint.';
$a->strings['Clear OAuth configuration'] = 'Effacer la configuration OAuth';
$a->strings['Cancel GNU Social Connection'] = 'Annuler la connexion à GNU Social';
$a->strings['Globally Available GNU Social OAuthKeys'] = 'Clés OAuth de GNU Social disponibles globalement';
$a->strings['There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below).'] = 'Il y a des paires des clés OAuth préconfigurées disponibles pour certains serveurs GNU Social. Si vous utilisez l\'une d\'elles, merci d\'utiliser ces identifiants. Si non, soyez libre de vous connecter à n\'importe quelle autre instance GNU Social (voir ci-dessous).';
$a->strings['Provide your own OAuth Credentials'] = 'Fournissez vos propres identifiants OAuth';
$a->strings['No consumer key pair for GNU Social found. Register your Friendica Account as a desktop application on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorite GNU Social installation.'] = 'Aucune paire de clés cliente pour GNU Social n\'a été trouvée. Enregistrez votre compte Friendica comme une application de bureau sur votre compte GNU Social, copiez la paire de clés cliente ici et saisissez la racine de base de l\'API. <br />Avant d\'enregistrer votre propre paire de clés, demandez à l\'administrateur si il y a déjà une paire de clés pour cette installation de Friendica sur votre installation GNU Social favorite.';
$a->strings['To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.'] = 'Pour vous connecter à votre compte GNU Social, cliquez sur le bouton ci-dessous pour obtenir un code de sécurité de GNU Social, que vous devrez copier dans le champ de saisie ci-dessous, puis validez le formulaire. Seules vos publications <strong>publiques</strong> seront relayées sur GNU Social.';
$a->strings['Log in with GNU Social'] = 'Se connecter avec GNU Social';
$a->strings['Cancel Connection Process'] = 'Annuler le processus de connexion';
$a->strings['Current GNU Social API is: %s'] = 'L\'API GNU Social actuelle est : %s';
$a->strings['OAuth Consumer Key'] = 'Clé d\'Utilisateur OAuth';
$a->strings['OAuth Consumer Secret'] = 'Secret d\'Utilisateur OAuth';
$a->strings['Base API Path (remember the trailing /)'] = 'Chemin de base de l\'API (n\'oubliez pas le / final)';
$a->strings['Copy the security code from GNU Social here'] = 'Coller le code de sécurité de GNU Social ici';
$a->strings['Allow posting to GNU Social'] = 'Autoriser la publication sur GNU Social';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Si activé, toutes vos publications <strong>publiques</strong> pourront être relayées sur le compte GNU Social associé. Vous pouvez choisir de faire cela par défaut (ici) ou individuellement pour chaque publication dans les options de publications au moment ou vous la créez.';
$a->strings['Post to GNU Social by default'] = 'Publier sur GNU Social par défaut';
$a->strings['GNU Social Import/Export/Mirror'] = 'Import/Export/Miroir GNU Social';
$a->strings['Site name'] = 'Nom du site';
$a->strings['Consumer Secret'] = 'Secret d\'Utilisateur';
$a->strings['Consumer Key'] = 'Clé d\'Utilisateur';
