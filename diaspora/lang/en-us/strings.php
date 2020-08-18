<?php

if(! function_exists("string_plural_select_en_us")) {
function string_plural_select_en_us($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Diaspora"] = "Post to Diaspora";
$a->strings["Please remember: You can always be reached from Diaspora with your Friendica handle <strong>%s</strong>. "] = "";
$a->strings["This connector is only meant if you still want to use your old Diaspora account for some time. "] = "";
$a->strings["However, it is preferred that you tell your Diaspora contacts the new handle <strong>%s</strong> instead."] = "";
$a->strings["All aspects"] = "";
$a->strings["Public"] = "";
$a->strings["Post to aspect:"] = "";
$a->strings["Connected with your Diaspora account <strong>%s</strong>"] = "";
$a->strings["Can't login to your Diaspora account. Please check handle (in the format user@domain.tld) and password."] = "";
$a->strings["Diaspora Export"] = "Diaspora Export";
$a->strings["Information"] = "";
$a->strings["Error"] = "";
$a->strings["Save Settings"] = "Save settings";
$a->strings["Enable Diaspora Post Addon"] = "Enable Diaspora export";
$a->strings["Diaspora handle"] = "";
$a->strings["Diaspora password"] = "Diaspora password";
$a->strings["Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it."] = "";
$a->strings["Post to Diaspora by default"] = "Post to Diaspora by default";
$a->strings["Diaspora settings updated."] = "";
$a->strings["Diaspora connector disabled."] = "";
