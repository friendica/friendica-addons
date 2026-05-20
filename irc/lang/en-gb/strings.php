<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'Lets you change the systemwide settings for channels that can be automatically joined and accessed via the side bar. Note that any changes you make here only affect the channel selection if you are logged in.';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Channel(s) to auto connect (comma separated)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'List of channels to automatically connect to when the app is launched.';
$a->strings['Popular Channels (comma separated)'] = 'Popular Channels (comma separated)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'List of popular channels, displayed at the side and hotlinked so they can be joined easily.';
$a->strings['IRC Settings'] = 'IRC Settings';
$a->strings['IRC Chatroom'] = 'IRC Chatroom';
$a->strings['Popular Channels'] = 'Popular Channels';
$a->strings['Save Settings'] = 'Save Settings';
