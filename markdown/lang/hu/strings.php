<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Markdown'] = 'Markdown';
$a->strings['Enable Markdown parsing'] = 'Markdown-feldolgozás engedélyezése';
$a->strings['If enabled, self created items will additionally be parsed via Markdown.'] = 'Ha engedélyezve van, akkor az önmagukat létrehozó elemek is fel lesznek dolgozva Markdown használatával.';
$a->strings['Save Settings'] = 'Beállítások mentése';
