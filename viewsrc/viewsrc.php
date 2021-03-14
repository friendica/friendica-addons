<?php
/**
 * Name: viewsrc
 * Description: Add "View Source" link to item context
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Hook;
use Friendica\DI;

function viewsrc_install() {
	Hook::register('item_photo_menu', 'addon/viewsrc/viewsrc.php', 'viewsrc_item_photo_menu');
	Hook::register('page_end', 'addon/viewsrc/viewsrc.php', 'viewsrc_page_end');
}

function viewsrc_page_end(&$a, &$o){
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

function viewsrc_item_photo_menu(&$a, &$b)
{
	if (!local_user()) {
		return;
	}

	$b['menu'] = array_merge([DI::l10n()->t('View Source') => DI::baseUrl()->get() . '/viewsrc/'. $b['item']['uri-id']], $b['menu']);
}
