<?php

use Friendica\Content\Text\HTML;
use Friendica\Content\Widget\ContactBlock;
use Friendica\DI;
use Friendica\Model\User;

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
	$owner = User::getOwnerDataById($conf['uid']);
	if (empty($owner)) {
		return;
	}

	$o = "";
	$o .= "<style>
		body {font-size: 0.8em; margin: 0px; padding: 0px;}
		#contact-block { overflow: hidden; height: auto; }
		.contact-block-h4 { float: left; margin: 0px; }
		.allcontact-link { float: right; margin: 0px; }
		.contact-block-content { clear:both; }
		.contact-block-div { display: block !important; float: left!important; width: 50px!important; height: 50px!important; margin: 2px!important;}

	</style>";
	$o .= _abs_url(ContactBlock::getHTML($owner));
	$o .= "<a href='".DI::baseUrl()->get().'/profile/'.$owner['nickname']."' target=new>". DI::l10n()->t('Get added to this list!') ."</a>";

	return $o;
}
