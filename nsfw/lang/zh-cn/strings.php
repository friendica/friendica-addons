<?php

if(! function_exists("string_plural_select_zh_CN")) {
function string_plural_select_zh_CN($n){
	$n = intval($n);
	return intval(0);
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = '这个插件在帖子中搜索指定的单词/文字，并将其折叠起来。它可以用来过滤标记为#NSFW的内容，这些内容在某些时间或地点可能被认为是不适当的，例如在工作中。它对隐藏不相关的或令人讨厌的内容也很有用，使其不被直接看到。';
$a->strings['Enable Content filter'] = '启用内容过滤';
$a->strings['Comma separated list of keywords to hide'] = '以逗号分隔需隐藏关键字列表';
$a->strings['Use /expression/ to provide regular expressions, #tag to specfically match hashtags (case-insensitive), or regular words (case-sensitive)'] = '使用 /expression/ 提供正则表达式，使用 #tag 专门匹配主题标签（不区分大小写），或使用正则词（区分大小写）';
$a->strings['Content Filter (NSFW and more)'] = '内容过滤 ( NSFW 及其他更多)';
$a->strings['Regular expression "%s" fails to compile'] = '正则表达式“%s”编译失败';
$a->strings['Filtered tag: %s'] = '已过滤标签：%s';
$a->strings['Filtered word: %s'] = '已过滤字词：%s';
