<?php

if (!function_exists('string_plural_select_fr')) {
    function string_plural_select_fr($n)
    {
        return $n > 1;
    }
}

$a->strings['Post to GNU Social'] = 'Publier sur GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = "Merci de contacter l'administrateur du site.<br />L'URL d'API fournie est invalide.";
$a->strings['We could not contact the GNU Social API with the Path you entered.'] = '';
$a->strings['GNU Social settings updated.'] = 'Paramètres du GNU Social mis à jour.';
$a->strings['GNU Social Import/Export/Mirror'] = '';
$a->strings['Globally Available GNU Social OAuthKeys'] = '';
$a->strings['There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below).'] = '';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Provide your own OAuth Credentials'] = '';
$a->strings['No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation.'] = '';
$a->strings['OAuth Consumer Key'] = '';
$a->strings['OAuth Consumer Secret'] = '';
$a->strings['Base API Path (remember the trailing /)'] = '';
$a->strings['To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.'] = '';
$a->strings['Log in with GNU Social'] = 'Se connecter avec GNU Social';
$a->strings['Copy the security code from GNU Social here'] = 'Coller le code de sécurité de GNU Social ici';
$a->strings['Cancel Connection Process'] = '';
$a->strings['Current GNU Social API is'] = "L'API actuelle de GNU Social est";
$a->strings['Cancel GNU Social Connection'] = '';
$a->strings['Currently connected to: '] = 'Actuellement connecté à :';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = '';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '';
$a->strings['Allow posting to GNU Social'] = 'Autoriser la publication sur GNU Social';
$a->strings['Send public postings to GNU Social by default'] = '';
$a->strings['Mirror all posts from GNU Social that are no replies or repeated messages'] = '';
$a->strings['Import the remote timeline'] = '';
$a->strings['Disabled'] = 'Désactiver';
$a->strings['Full Timeline'] = 'Timeline complète';
$a->strings['Only Mentions'] = '';
$a->strings['Clear OAuth configuration'] = '';
$a->strings['Site name'] = 'Nom du site';
$a->strings['Consumer Secret'] = '';
$a->strings['Consumer Key'] = '';
