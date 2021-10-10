<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New Member'] = 'Új tag';
$a->strings['Tips for New Members'] = 'Tippek új tagoknak';
$a->strings['Global Support Forum'] = 'Globális támogató fórum';
$a->strings['Local Support Forum'] = 'Helyi támogató fórum';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Message'] = 'Üzenet';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Az Ön üzenete az új tagoknak. Itt használhat BBCode-ot.';
$a->strings['Add a link to global support forum'] = 'A globális támogató fórumra mutató hivatkozás hozzáadása';
$a->strings['Should a link to the global support forum be displayed?'] = 'Meg kell jeleníteni a globális támogató fórumra mutató hivatkozást?';
$a->strings['Add a link to the local support forum'] = 'A helyi támogató fórumra mutató hivatkozás hozzáadása';
$a->strings['If you have a local support forum and want to have a link displayed in the widget, check this box.'] = 'Ha van helyi támogató fóruma és szeretne egy hivatkozást megjeleníteni a felületi elemben, akkor jelölje be azt a négyzetet.';
$a->strings['Name of the local support group'] = 'A helyi támogató csoport neve';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'Ha bejelölte a fentit, akkor itt adja meg a helyi támogató csoport <em>becenevét</em> (például segítők)';
