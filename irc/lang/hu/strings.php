<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["IRC Settings"] = "IRC beállítások";
$a->strings["Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in."] = "Itt változtathatja meg a csatornák rendszerszintű beállításait, hogy automatikusan csatlakozzon és hozzáférjen az oldalsávon keresztül. Ne feledje, hogy az itt elvégzett változtatások csak akkor vannak hatással a csatornakiválasztásra, ha be van jelentkezve.";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Channel(s) to auto connect (comma separated)"] = "Csatornák az automatikus kapcsolódáshoz (vesszővel elválasztva)";
$a->strings["List of channels that shall automatically connected to when the app is launched."] = "Csatornák listája, amelyekhez automatikusan lehet kapcsolódni, ha az alkalmazást elindították.";
$a->strings["Popular Channels (comma separated)"] = "Népszerű csatornák (vesszővel elválasztva)";
$a->strings["List of popular channels, will be displayed at the side and hotlinked for easy joining."] = "Népszerű csatornák listája, amelyek oldalt lesznek megjelenítve, és gyors hivatkozás lesz rájuk az egyszerű csatlakozáshoz.";
$a->strings["IRC Chatroom"] = "IRC csevegőszoba";
$a->strings["Popular Channels"] = "Népszerű csatornák";
