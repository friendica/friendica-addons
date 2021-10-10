<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'] = 'Mit dem MathJax Addon können mathematische Formeln, die mit LaTeX geschrieben wurden, dargestellt werden. Die Formel wird mit den üblichen $$ oder einem eqnarray-Block gekennzeichnet. Formeln werden in allen Beiträgen auf deiner Pinnwand, dem Netzwerk-Stream sowie privaten Nachrichten dargestellt.';
$a->strings['Use the MathJax renderer'] = 'MathJax verwenden';
$a->strings['Save Settings'] = 'Einstellungen speichern';
