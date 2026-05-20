<?php

if(! function_exists("string_plural_select_zh_CN")) {
function string_plural_select_zh_CN($n){
	$n = intval($n);
	return intval(0);
}}
$a->strings['Post to Diaspora'] = '发到 Diaspora';
$a->strings['Public'] = '公开';
$a->strings['Can\'t login to your Diaspora account. Please check handle (in the format user@domain.tld) and password.'] = '无法登录您的 Diaspora 帐户。请检查格式（格式为 user@domain.tld）和密码。';
$a->strings['Information'] = '信息';
$a->strings['Error'] = '错误';
$a->strings['Enable Diaspora Post Addon'] = '启用 Diaspora 发文插件';
$a->strings['Diaspora password'] = 'Diaspora 密码';
$a->strings['Privacy notice: Your Diaspora password will be stored unencrypted to authenticate you with your Diaspora pod. This means your Friendica node administrator can have access to it.'] = '隐私声明：您的 Diaspora 密码将以明文存储，以使用 Diaspora pod 对您身份验证。这意味着您的站点管理员可以访问它。';
$a->strings['Post to Diaspora by default'] = '默认发文到 Diaspora';
$a->strings['Diaspora Export'] = 'Diaspora 导出';
