<?php

use Friendica\Content\Text\HTML;
use Friendica\DI;

function friendheader_widget_name()
{
	return "Shows friends as a bar";
}
function friendheader_widget_help()
{
	return "";
}

function friendheader_widget_args()
{
	return [];
}

function friendheader_widget_size()
{
	return ['780px','140px'];
}


function friendheader_widget_content(&$a, $conf)
{
	$r = q("SELECT `profile`.`uid` AS `profile_uid`, `profile`.* , `user`.* FROM `profile`
			LEFT JOIN `user` ON `profile`.`uid` = `user`.`uid`
			WHERE `user`.`uid` = %s AND `profile`.`is-default` = 1 LIMIT 1",
		intval($conf['uid'])
	);
	if (!count($r)) {
		return;
	}

	$a->profile = $r[0];

	$o = "";
	$o .= "<style>
		body {font-size: 0.8em; margin: 0px; padding: 0px;}
		#contact-block { overflow: hidden; height: auto; }
		.contact-block-h4 { float: left; margin: 0px; }
		.allcontact-link { float: right; margin: 0px; }
		.contact-block-content { clear:both; }
		.contact-block-div { display: block !important; float: left!important; width: 50px!important; height: 50px!important; margin: 2px!important;}

	</style>";
	$o .= _abs_url(HTML::contactBlock());
	$o .= "<a href='".DI::baseUrl()->get().'/profile/'.$a->profile['nickname']."' target=new>". DI::l10n()->t('Get added to this list!') ."</a>";

	return $o;
}
