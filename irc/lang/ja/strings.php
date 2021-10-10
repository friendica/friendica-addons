<?php

if(! function_exists("string_plural_select_ja")) {
function string_plural_select_ja($n){
	$n = intval($n);
	return intval(0);
}}
$a->strings['IRC Settings'] = 'IRC設定';
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'ここでは、システム全体の設定を変更して、自動的にチャンネルに参加したり、サイドバーからアクセスしたりすることができます。なお、ここで行った変更は、ログインしている場合にのみチャンネルの選択に影響します。';
$a->strings['Save Settings'] = '設定を保存';
$a->strings['Channel(s) to auto connect (comma separated)'] = '自動接続するチャンネル（カンマで区切る）';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'アプリ起動時に自動接続されるチャンネルの一覧。';
