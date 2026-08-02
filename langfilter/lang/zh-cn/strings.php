<?php

if(! function_exists("string_plural_select_zh_cn")) {
function string_plural_select_zh_cn($n){
	$n = intval($n);
	return intval(0);
}}
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = '此插件试图辨别文章的写作语言。如果语言与下面给定的均不符合，则文章会被折叠隐藏起来。';
$a->strings['Use the language filter'] = '使用语言过滤器';
$a->strings['Able to read'] = '想要显示的语言';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = '语言列表（ISO 639-1 缩写代码），使用英文逗号分隔。例如：“de,it,zh”';
$a->strings['Minimum confidence in language detection'] = '语言识别阈值';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = '语言识别阈值（0－100）。语言识别结果低于该阈值的帖子将不会被折叠。';
$a->strings['Minimum length of message body'] = '语言过滤帖子所需最小字符个数';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = '语言过滤帖子所需最小字符个数。低于该数字的帖子将不会被过滤。注意：对于字符个数小于200的帖子，语言检测功能将不够稳定。';
$a->strings['Language Filter'] = '语言过滤器';
$a->strings['Save Settings'] = '保存设置';
$a->strings['Filtered language: %s'] = '已过滤的语言：%s';
