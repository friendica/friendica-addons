<?php

if(! function_exists("string_plural_select_gd")) {
function string_plural_select_gd($n){
	$n = intval($n);
	if (($n==1 || $n==11)) { return 0; } else if (($n==2 || $n==12)) { return 1; } else if (($n > 2 && $n < 20)) { return 2; } else  { return 3; }
}}
$a->strings['Enable Markdown parsing'] = 'Cuir parsadh Markdown an comas';
$a->strings['If enabled, adds Markdown support to the Compose Post form.'] = 'Ma tha seo an comas, cuiridh e taic ri Markdown ri foirm sgrìobhadh puist.';
$a->strings['Markdown Settings'] = 'Roghainnean Markdown';
