<?php

if(! function_exists("string_plural_select_ja")) {
function string_plural_select_ja($n){
	$n = intval($n);
	return intval(0);
}}
;
$a->strings["Language Filter"] = "言語フィルタ";
$a->strings["This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them."] = "このアドオンは、投稿が書かれている言語の特定を試みます。以下に指定されたどの言語にも一致しない場合、投稿は折り畳まれて隠されます。";
$a->strings["Use the language filter"] = "言語フィルタを使う";
$a->strings["Able to read"] = "読めるもの";
$a->strings["List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example \"de,it\"."] = "あなたが話す言語の略語（ISO 639-1コード）の一覧をカンマで区切ってください。例えば、\"de,it\"。";
$a->strings["Minimum confidence in language detection"] = "言語検出の最低信頼度";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "言語検出が正しいことを示す最小の信頼度を0から100までで指定します。言語検出の信頼度がこの百分率の値以下の場合、投稿はフィルタリングされません。";
$a->strings["Minimum length of message body"] = "メッセージ本文の最低の長さ";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "フィルタを使用するためのメッセージ本文の最小文字数。これより短い投稿はフィルタリングされません。注：短いコンテンツ（200文字未満）の場合、言語検出は信頼できません。";
$a->strings["Save Settings"] = "設定を保存";
