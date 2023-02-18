<?php
/**
 * Name: viewsrc
 * Description: Add "View Source" link to item context
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\DI;

function viewsrc_install()
{
	Hook::register('item_photo_menu', 'addon/viewsrc/viewsrc.php', 'viewsrc_item_photo_menu');
	Hook::register('page_end', 'addon/viewsrc/viewsrc.php', 'viewsrc_page_end');
}

function viewsrc_page_end(string &$o)
{
	DI::page()['htmlhead'] .= <<< EOS
	<script>
		$(function(){
			$('a[href*="/viewsrc/"]').each(function() {
				$(this).colorbox($(this).attr('href'));
			});
		});
	</script>
EOS;
}

function viewsrc_item_photo_menu(array &$b)
{
	if (!DI::userSession()->getLocalUserId()) {
		return;
	}

	$b['menu'] = array_merge([DI::l10n()->t('View Source') => DI::baseUrl() . '/viewsrc/'. $b['item']['uri-id']], $b['menu']);
}
