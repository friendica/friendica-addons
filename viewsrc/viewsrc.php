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
			$('a[href*="/viewsrc/"]').fancybox();
		});
	</script>
EOS;
}

function viewsrc_item_photo_menu(&$a,&$b) {
	if(! local_user())
		return;
	$b['menu'] = array_merge( array( t('View Source') => $a->get_baseurl() . '/viewsrc/'. $b['item']['id']), $b['menu']);

}
