<?php
/**
 * Name: viewsrc
 * Description: Add "View Source" link to item context
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *
 */
use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Database\DBA;

function viewsrc_install() {
	Hook::register('item_photo_menu', 'addon/viewsrc/viewsrc.php', 'viewsrc_item_photo_menu');
	Hook::register('page_end', 'addon/viewsrc/viewsrc.php', 'viewsrc_page_end');
}


function viewsrc_uninstall() {
	Hook::unregister('item_photo_menu', 'addon/viewsrc/viewsrc.php', 'viewsrc_item_photo_menu');
	Hook::unregister('page_end', 'addon/viewsrc/viewsrc.php', 'viewsrc_page_end');

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

	if (local_user() != $b['item']['uid']) {
		$item = Item::selectFirstForUser(local_user(), ['id'], ['uid' => local_user(), 'guid' => $b['item']['guid']]);
		if (!DBA::isResult($item)) {
			return;
		}

		$item_id = $item['id'];
	} else {
		$item_id = $b['item']['id'];
	}

	$b['menu'] = array_merge([L10n::t('View Source') => DI::baseUrl()->get() . '/viewsrc/'. $item_id], $b['menu']);

	//if((! local_user()) || (local_user() != $b['item']['uid']))
	//	return;

	//$b['menu'] = array_merge(array(L10n::t('View Source') => $a->getBaseURL() . '/viewsrc/'. $b['item']['id']), $b['menu']);
}
