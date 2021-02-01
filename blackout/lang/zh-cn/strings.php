<?php

if(! function_exists("string_plural_select_zh_cn")) {
function string_plural_select_zh_cn($n){
	$n = intval($n);
	return intval(0);
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "结束日期早于开始日期，您应该修复此问题";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "请仔细检查一下当前的维护设置。将从<strong>%s</strong>开始结束于<strong>%s</strong>。";
$a->strings["Save Settings"] = "保存设置";
$a->strings["Redirect URL"] = "重定向URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "所有来自web的访问者都将重定向到此URL";
$a->strings["Begin of the Blackout"] = "开始维护";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "格式为<tt>YYYY-MM-DD HH：MM</tt>；<em>YYYY年</em>、<em>MM月</em>、<em>DD日</em>、<em>HH</em>小时和<em>MM</em>分钟";
$a->strings["End of the Blackout"] = "结束维护";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>注意</strong>：从您按下提交按钮的那一刻起，重定向将处于活动状态。当前登录的用户<strong>不会</strong>被驱逐，但如果仍处于维护状态，则在注销后不能再次登录。";
