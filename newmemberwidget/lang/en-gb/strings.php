<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['New Member'] = 'New Member';
$a->strings['Tips for New Members'] = 'Hints & Tips for New Members';
$a->strings['Save Settings'] = 'Save Settings';
$a->strings['Message'] = 'Message';
$a->strings['Your message for new members. You can use bbcode here.'] = 'Your message for new members. You can use BBCode here.';
$a->strings['Name of the local support group'] = 'Name of the local support group';
$a->strings['If you checked the above, specify the <em>nickname</em> of the local support group here (i.e. helpers)'] = 'If you ticked the above option, please specify the <em>nickname</em> of the local support group here (e.g. helpers)';
