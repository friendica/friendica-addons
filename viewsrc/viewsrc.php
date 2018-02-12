<?php


/**
 * Name: viewsrc
 * Description: Add "View Source" link to item context
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * 
 */

function viewsrc_install() {
	register_hook('item_photo_menu', 'addon/viewsrc/viewsrc.php', 'viewsrc_item_photo_menu');
	register_hook('page_end', 'addon/viewsrc/viewsrc.php', 'viewsrc_page_end');
}


function viewsrc_uninstall() {
	unregister_hook('item_photo_menu', 'addon/viewsrc/viewsrc.php', 'viewsrc_item_photo_menu');
	unregister_hook('page_end', 'addon/viewsrc/viewsrc.php', 'viewsrc_page_end');

}

function viewsrc_page_end(&$a, &$o){
	$a->page['htmlhead'] .= <<< EOS
	<script>
		$(function(){
			$('a[href*="/viewsrc/"]').each(function() {
				$(this).colorbox($(this).attr('href'));
			});
		});
	</script>
EOS;
}

function viewsrc_item_photo_menu(&$a,&$b) {
	if(!local_user())
		return;

	if (local_user() != $b['item']['uid']) {
		$r = q("SELECT `id` FROM `item` WHERE `uid` = %d AND `guid` = '%s'",
				intval(local_user()), dbesc($b['item']['guid']));

		if (!$r)
			return;

		$item_id = $r[0]['id'];

	} else
		$item_id = $b['item']['id'];

	$b['menu'] = array_merge( array( t('View Source') => $a->get_baseurl() . '/viewsrc/'. $item_id), $b['menu']);

	//if((! local_user()) || (local_user() != $b['item']['uid']))
	//	return;

	//$b['menu'] = array_merge( array( t('View Source') => $a->get_baseurl() . '/viewsrc/'. $b['item']['id']), $b['menu']);

}
