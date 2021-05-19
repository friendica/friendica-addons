<?php

if(! function_exists("string_plural_select_ja")) {
function string_plural_select_ja($n){
	$n = intval($n);
	return intval(0);
}}
;
$a->strings["Post to Twitter"] = "Twitterに投稿";
$a->strings["You submitted an empty PIN, please Sign In with Twitter again to get a new one."] = "空のPINを送信しました。もう一度Twitterでサインインして新しいPINを取得してください。";
$a->strings["Twitter Import/Export/Mirror"] = "Twitterインポート/エクスポート/ミラー";
$a->strings["No consumer key pair for Twitter found. Please contact your site administrator."] = "Twitterのコンシューマキーペアが見つかりません。サイト管理者に連絡してください。";
$a->strings["At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter."] = "このFriendicaインスタンスでは、Twitterアドオンは有効になっていますが、アカウントをTwitterアカウントにまだ接続していません。これを行うには、下のボタンをクリックしてTwitterからPINを取得し、それを下の入力ボックスにコピーしてフォームを送信する必要があります。 <strong>一般公開</strong>投稿のみがTwitterに投稿されます。";
$a->strings["Log in with Twitter"] = "Twitterでログイン";
$a->strings["Copy the PIN from Twitter here"] = "ここからTwitterからPINをコピーします";
$a->strings["Save Settings"] = "設定を保存する";
$a->strings["An error occured: "] = "エラーが発生しました：";
$a->strings["Currently connected to: "] = "現在接続中：";
$a->strings["Disconnect"] = "切断する";
$a->strings["Allow posting to Twitter"] = "Twitterへの投稿を許可する";
$a->strings["If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry."] = "有効にすると、すべての<strong>一般公開</strong>投稿を、関連づけたTwitterアカウントに投稿できます。デフォルト（ここ）で行うか、エントリを書き込む際に投稿オプションですべての投稿を個別に行うかを選択できます。";
$a->strings["<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted."] = "<strong>注</strong>：プライバシー設定（<em>未知の視聴者からプロフィールの詳細を非表示にしますか？</em>）により、Twitterに中継・公開される投稿内のリンクは、プロフィールへのアクセスが制限されている訪問者に対して空白ページを表示します。";
$a->strings["Send public postings to Twitter by default"] = "デフォルトでTwitterに一般公開投稿を送信する";
$a->strings["Mirror all posts from twitter that are no replies"] = "返信がないTwitterのすべての投稿をミラーリングする";
$a->strings["Import the remote timeline"] = "リモートタイムラインをインポートする";
$a->strings["Automatically create contacts"] = "連絡先を自動的に作成する";
$a->strings["This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here. However if enabled, you cannot merely remove a twitter contact from the Friendica contact list, as it will recreate this contact when they post again."] = "これにより、Twitterネットワーク経由で既存の連絡先からメッセージを受信するとすぐに、Friendicaに連絡先が自動的に作成されます。これを有効にしない場合、ここで投稿を表示するFriendicaのTwitter連絡先を手動で追加する必要があります。ただし、有効にした場合、Twitterの連絡先をFriendicaの連絡先リストから単に削除することはできません。再送信するとこの連絡先が再作成されるためです。";
$a->strings["Consumer key"] = "コンシューマ キー";
$a->strings["Consumer secret"] = "コンシューマ シークレット";
