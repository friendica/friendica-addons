<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Cette extension recherche des mots/textes spécifiés dans les publications et les masque. Elle peut être utilisée pour filtrer le contenu étiqueté par exemple avec #NSFW qui peut être considéré comme inapproprié à certains moments ou endroits, comme par exemple au travail. Elle est aussi utile pour cacher du contenu non pertinent ou ennuyeux d\'une vue directe.';
$a->strings['Enable Content filter'] = 'Activer le filtrage de contenu';
$a->strings['Comma separated list of keywords to hide'] = 'Liste de mots-clés - séparés par des virgules - à cacher';
$a->strings['Use /expression/ to provide regular expressions, #tag to specfically match hashtags (case-insensitive), or regular words (case-sensitive)'] = 'Utiliser /expression/ pour fournir des expressions régulières, #tag pour correspondre à un mot-dièse (hashtag, insensible à la casse), ou des mots classiques (sensible à la casse)';
$a->strings['Content Filter (NSFW and more)'] = 'Filtre de contenu (NSFW et autres)';
$a->strings['Regular expression "%s" fails to compile'] = 'La compilation de l\'expression régulière "%s" a échoué';
$a->strings['Filtered tag: %s'] = 'Tag filtré: %s';
$a->strings['Filtered word: %s'] = 'Mot filtré: %s';
