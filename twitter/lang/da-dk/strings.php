<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Læg op på Twitter';
$a->strings['You submitted an empty PIN, please Sign In with Twitter again to get a new one.'] = 'Du indsendte en tom PIN, log venligst ind med Twitter igen og få en ny en.';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'Intet "consumer key pair" fundet for Twitter. Kontakt venligst din sides administrator.';
$a->strings['At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'På denne Friendica instans er Twitter-tilføjelsen slået til, men du har ikke forbundet din konto til din Twitter-konto endnu. For at gøre det, skal du klikke på knappen herunder for at få en PIN fra Twitter, som du så skal kopiere ind i input-boksen og indsende . Det er kun dine <strong>offentlige</strong> opslag som vil blive lagt op på Twitter.';
$a->strings['Log in with Twitter'] = 'Log ind med Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Kopier din PIN fra Twitter ind her';
$a->strings['An error occured: '] = 'Der opstod en fejl:';
$a->strings['Currently connected to: <a href="https://twitter.com/%1$s" target="_twitter">%1$s</a>'] = 'I øjeblikket forbundet til: <a href="https://twitter.com/%1$s" target="_twitter">%1$s</a>';
$a->strings['<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Note</strong>: Grundet dine privatlivsindstillinger (<em>Skjul dine profildetaljer fra ukendte besøgende?</em>), vil linket, som potentielt kan være inkluderet i offentlige opslag på Twitter, lede tilbage til en blank side som informerer den besøgende om at adgang til din profil er begrænset.';
$a->strings['Invalid Twitter info'] = 'Ugyldig Twitter information';
$a->strings['Disconnect'] = 'Afbryd forbindelsen';
$a->strings['Allow posting to Twitter'] = 'Tillad at lave opslag på Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Hvis aktiveret, kan alle dine <strong>offentlige</strong> opslag blive lagt op på den associerede Twitter konto. Du kan vælge at gøre dette automatisk (her), eller separat for hvert opslag in valgmulighederne når du skriver opslaget.';
$a->strings['Send public postings to Twitter by default'] = 'Send som standard offentlige opslag til Twitter';
$a->strings['Mirror all posts from twitter that are no replies'] = 'Spejl alle opslag fra Twitter som ikke er svar';
$a->strings['Import the remote timeline'] = 'Importér den eksterne tidslinje';
$a->strings['Automatically create contacts'] = 'Opret automatisk kontakter';
$a->strings['This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here.'] = 'Dette vil automatisk skabe en kontakt i Friendica, så snart du modtager en besked fra en eksisterende kontakt via Twitter-netværket. Hvis du ikke slår dette til, skal du manuelt tilføje de Twitter kontakter i Friendica, som du gerne vil se opslag fra her.';
$a->strings['Twitter Import/Export/Mirror'] = 'Twitter Import/Eksport/Spejl';
$a->strings['Please connect a Twitter account in your Social Network settings to import Twitter posts.'] = 'Forbind venligst en Twitter konto i dine Sociale Netværk indstiliinger for at importere Twitter opslag.';
$a->strings['Twitter post not found.'] = 'Twitter opslag kunne ikke opstøves.';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Consumer key'] = '"Consumer" nøgle';
$a->strings['Consumer secret'] = '"Consumer" hemmelighed';
$a->strings['%s on Twitter'] = '%s på Twitter';
