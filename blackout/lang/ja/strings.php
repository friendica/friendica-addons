<?php

if(! function_exists("string_plural_select_ja")) {
function string_plural_select_ja($n){
	$n = intval($n);
	return 0;;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "終了日はブラックアウトの開始日より前です。これを修正する必要があります";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "ブラックアウトの現在の設定を再確認してください。開始は<strong> %s </strong>で、終了は<strong> %s </strong>です。";
$a->strings["Save Settings"] = "設定を保存する";
$a->strings["Redirect URL"] = "リダイレクト URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "Webからのすべての訪問者はこのURLにリダイレクトされます";
$a->strings["Begin of the Blackout"] = "ブラックアウトの始まり";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "形式は<tt> YYYY-MM-DD hh：mm </tt>です。 <em> YYYY </em>年、<em> MM </em>月、<em> DD </em>日、<em> hh </em>時間と<em> mm </em>分。";
$a->strings["End of the Blackout"] = "ブラックアウトの終わり";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>備考</strong>：送信ボタンを押した時点からリダイレクトが有効になります。現在ログインしているユーザーは<strong>リダイレクトされません</strong>が、ブラックアウトが有効な間はログアウト後再度ログインできなくなります。";
