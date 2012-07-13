<?php

/**
 * Name: Libertree Post Connector
 * Description: Post to libertree accounts
 * Version: 1.0
 * Author: Tony Baldwin <https://free-haven.org/u/tony>
 */

function libertree_install() {
    register_hook('post_local',           'addon/libertree/libertree.php', 'libertree_post_local');
    register_hook('notifier_normal',      'addon/libertree/libertree.php', 'libertree_send');
    register_hook('jot_networks',         'addon/libertree/libertree.php', 'libertree_jot_nets');
    register_hook('connector_settings',      'addon/libertree/libertree.php', 'libertree_settings');
    register_hook('connector_settings_post', 'addon/libertree/libertree.php', 'libertree_settings_post');

}
function libertree_uninstall() {
    unregister_hook('post_local',       'addon/libertree/libertree.php', 'libertree_post_local');
    unregister_hook('notifier_normal',  'addon/libertree/libertree.php', 'libertree_send');
    unregister_hook('jot_networks',     'addon/libertree/libertree.php', 'libertree_jot_nets');
    unregister_hook('connector_settings',      'addon/libertree/libertree.php', 'libertree_settings');
    unregister_hook('connector_settings_post', 'addon/libertree/libertree.php', 'libertree_settings_post');
}


function libertree_jot_nets(&$a,&$b) {
    if(! local_user())
        return;

    $ltree_post = get_pconfig(local_user(),'libertree','post');
    if(intval($ltree_post) == 1) {
        $ltree_defpost = get_pconfig(local_user(),'libertree','post_by_default');
        $selected = ((intval($ltree_defpost) == 1) ? ' checked="checked" ' : '');
        $b .= '<div class="profile-jot-net"><input type="checkbox" name="libertree_enable"' . $selected . ' value="1" /> '
            . t('Post to libertree') . '</div>';
    }
}


function libertree_settings(&$a,&$s) {

    if(! local_user())
        return;

    /* Add our stylesheet to the page so we can make our settings look nice */

    $a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/libertree/libertree.css' . '" media="all" />' . "\r\n";

    /* Get the current state of our config variables */

    $enabled = get_pconfig(local_user(),'libertree','post');

    $checked = (($enabled) ? ' checked="checked" ' : '');

    $def_enabled = get_pconfig(local_user(),'libertree','post_by_default');

    $def_checked = (($def_enabled) ? ' checked="checked" ' : '');

    $ltree_api_token = get_pconfig(local_user(), 'libertree', 'libertree_api_token');
    $ltree_url = get_pconfig(local_user(), 'libertree', 'libertree_url');


    /* Add some HTML to the existing form */

    $s .= '<div class="settings-block">';
    $s .= '<h3>' . t('libertree Post Settings') . '</h3>';
    $s .= '<div id="libertree-enable-wrapper">';
    $s .= '<label id="libertree-enable-label" for="libertree-checkbox">' . t('Enable Libertree Post Plugin') . '</label>';
    $s .= '<input id="libertree-checkbox" type="checkbox" name="libertree" value="1" ' . $checked . '/>';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="libertree-api_token-wrapper">';
    $s .= '<label id="libertree-api_token-label" for="libertree-api_token">' . t('Libertree API token') . '</label>';
    $s .= '<input id="libertree-api_token" type="text" name="libertree_api_token" value="' . $ltree_api_token . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="libertree-url-wrapper">';
    $s .= '<label id="libertree-url-label" for="libertree-url">' . t('Libertree site URL') . '</label>';
    $s .= '<input id="libertree-url" type="text" name="libertree_url" value="' . $ltree_url . '" />';
    $s .= '</div><div class="clear"></div>';

    $s .= '<div id="libertree-bydefault-wrapper">';
    $s .= '<label id="libertree-bydefault-label" for="libertree-bydefault">' . t('Post to Libertree by default') . '</label>';
    $s .= '<input id="libertree-bydefault" type="checkbox" name="libertree_bydefault" value="1" ' . $def_checked . '/>';
    $s .= '</div><div class="clear"></div>';

    /* provide a submit button */

    $s .= '<div class="settings-submit-wrapper" ><input type="submit" id="libertree-submit" name="libertree-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}


function libertree_settings_post(&$a,&$b) {

	if(x($_POST,'libertree-submit')) {

		set_pconfig(local_user(),'libertree','post',intval($_POST['libertree']));
		set_pconfig(local_user(),'libertree','post_by_default',intval($_POST['libertree_bydefault']));
		set_pconfig(local_user(),'libertree','libertree_api_token',trim($_POST['libertree_api_token']));
		set_pconfig(local_user(),'libertree','libertree_url',trim($_POST['libertree_url']));

	}

}

function libertree_post_local(&$a,&$b) {

	// This can probably be changed to allow editing by pointing to a different API endpoint

	if($b['edit'])
		return;

	if((! local_user()) || (local_user() != $b['uid']))
		return;

	if($b['private'] || $b['parent'])
		return;

	$ltree_post   = intval(get_pconfig(local_user(),'libertree','post'));

	$ltree_enable = (($ltree_post && x($_REQUEST,'libertree_enable')) ? intval($_REQUEST['libertree_enable']) : 0);

	if($_REQUEST['api_source'] && intval(get_pconfig(local_user(),'libertree','post_by_default')))
		$ltree_enable = 1;

    if(! $ltree_enable)
       return;

    if(strlen($b['postopts']))
       $b['postopts'] .= ',';
     $b['postopts'] .= 'libertree';
}




function libertree_send(&$a,&$b) {

	logger('libertree_send: invoked');

    if($b['deleted'] || $b['private'] || ($b['created'] !== $b['edited']))
        return;

    if(! strstr($b['postopts'],'libertree'))
        return;

    if($b['parent'] != $b['id'])
        return;


	$ltree_api_token = get_pconfig($b['uid'],'libertree','libertree_api_token');
	$ltree_url = get_pconfig($b['uid'],'libertree','libertree_url');
	$ltree_blog = "$ltree_url/api/v1/posts/create/?token=$ltree_api_token";
	$ltree_source = "Friendica";
	if($ltree_url && $ltree_api_token && $ltree_blog && $ltree_source) {

		require_once('include/bb2diaspora.php');
		$tag_arr = array();
		$tags = '';
		$x = preg_match_all('/\#\[(.*?)\](.*?)\[/',$b['tag'],$matches,PREG_SET_ORDER);

		if($x) {
			foreach($matches as $mtch) {
				$tag_arr[] = $mtch[2];
			}
		}
		if(count($tag_arr))
			$tags = implode(',',$tag_arr);

		$title = $b['title'];
		$body = $b['body'];
		// Insert a newline before and after a quote
		$body = str_ireplace("[quote", "\n\n[quote", $body);
		$body = str_ireplace("[/quote]", "[/quote]\n\n", $body);

		// Removal of tags and mentions
		// #-tags
		$body = preg_replace('/#\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '#$2', $body);
 		// @-mentions
		$body = preg_replace('/@\[url\=(\w+.*?)\](\w+.*?)\[\/url\]/i', '@$2', $body);

		// remove multiple newlines
		do {
			$oldbody = $body;
                        $body = str_replace("\n\n\n", "\n\n", $body);
                } while ($oldbody != $body);

		// convert to markdown
		$body = bb2diaspora($body);

		// Adding the title
		if(strlen($title))
			$body = "## ".html_entity_decode($title)."\n\n".$body;


		$params = array(
			'text' => $body,
			'source' => $ltree_source
		//	'token' => $ltree_api_token
		);

		$result = post_url($ltree_blog,$params);
		logger('libertree: ' . $result);

	}
}

