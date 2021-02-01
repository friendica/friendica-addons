<?php

if(! function_exists("string_plural_select_zh_cn")) {
function string_plural_select_zh_cn($n){
	$n = intval($n);
	return intval(0);
}}
;
$a->strings["Post to Twitter"] = "发帖到Twitter";
$a->strings["You submitted an empty PIN, please Sign In with Twitter again to get a new one."] = "您提交的PIN为空，请重新登录Twitter获取新PIN。";
$a->strings["Twitter settings updated."] = "已更新Twitter设置。";
$a->strings["Twitter Import/Export/Mirror"] = "Twitter导入/导出/镜像";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "找不到Twitter的使用者密钥。请与您的站点管理员联系。";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "在此Friendica实例中，Twitter插件已启用，但您尚未将您的帐户连接到您的Twitter帐户。要执行此操作，请单击下面的按钮从Twitter获取PIN，您必须将其复制到下面的输入框中并提交表单。只有您的公共帖子才会发布到Twitter上。";
$a->strings["Log in with Twitter"] = "使用Twitter登录";
$a->strings["Copy the PIN from Twitter here"] = "将Twitter上的PIN复制到此处";
$a->strings["Save Settings"] = "保存设置";
$a->strings["Currently connected to: "] = "当前连接到：";
$a->strings["Disconnect"] = "断开连接";
$a->strings["Allow posting to Twitter"] = "允許發佈到Twitter";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "如果启用，您所有的公开帖子都可以发布到关联的Twitter帐户。在写入条目时，您可以选择默认(此处)，也可以在过帐选项中分别为每个过帐选择这样做。";
$a->strings["<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "注意：由于您的隐私设置(向未知观众隐藏您的个人资料详细信息？)。转发到Twitter的公共帖子中可能包含的链接将把访问者引导到一个空白页面，通知访问者访问您的个人资料已受到限制。";
$a->strings["Send public postings to Twitter by default"] = "默认情况下将公共帖子发送到Twitter";
$a->strings["Mirror all posts from twitter that are no replies"] = "镜像来自Twitter的所有未回复的帖子";
$a->strings["Import the remote timeline"] = "导入远程时间线";
$a->strings["Automatically create contacts"] = "自动创建联系人";
$a->strings["This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again."] = "一旦您通过Twitter网络收到来自现有联系人的消息，这将在Friendica中自动创建一个联系人。如果您不启用此功能，则需要在Friendica中手动添加您希望在此处查看其帖子的Twitter联系人。但是，如果启用，您不能仅将Twitter联系人从Friendica联系人列表中删除，因为它会在他们再次发帖时重新创建此联系人。";
$a->strings["Twitter post failed. Queued for retry."] = "推特发帖失败。已排队等待重试。";
$a->strings["Settings updated."] = "设置已更新。";
$a->strings["Consumer key"] = "使用者密钥";
$a->strings["Consumer secret"] = "使用者机密";
