<?php

function like_widget_name()
{
    return 'Shows likes';
}
function like_widget_help()
{
    return 'Search first item which contains <em>KEY</em> and print like/dislike count';
}

function like_widget_args()
{
    return array('KEY');
}

function like_widget_size()
{
    return array('60px', '20px');
}

function like_widget_content(&$a, $conf)
{
    $args = explode(',', $_GET['a']);

    $baseq = "SELECT COUNT(`item`.`id`) as `c`, `p`.`id`
					FROM `item`, 
						(SELECT `i`.`id` FROM `item` as `i` WHERE 
							`i`.`visible` = 1 AND `i`.`deleted` = 0
							AND (( `i`.`wall` = 1 AND `i`.`allow_cid` = ''  
									AND `i`.`allow_gid` = '' 
									AND `i`.`deny_cid`  = '' 
									AND `i`.`deny_gid`  = '' ) 
								  OR `i`.`uid` = %d )
							AND `i`.`body` LIKE '%%%s%%' LIMIT 1) as `p`
					WHERE `item`.`parent` = `p`.`id` ";

    // count likes
    $r = q($baseq."AND `item`.`verb` = 'http://activitystrea.ms/schema/1.0/like'",
            intval($conf['uid']),
            dbesc($args[0])
    );
    $likes = $r[0]['c'];
    $iid = $r[0]['id'];

    // count dislikes
    $r = q($baseq."AND `item`.`verb` = 'http://purl.org/macgirvin/dfrn/1.0/dislike'",
            intval($conf['uid']),
            dbesc($args[0])
    );
    $dislikes = $r[0]['c'];

    require_once 'include/conversation.php';

    $o = '';

//	$t = file_get_contents( dirname(__file__). "/widget_like.tpl" );
    $t = get_markup_template('widget_like.tpl', 'addon/widgets/');
    $o .= replace_macros($t, array(
        '$like' => $likes,
        '$strlike' => sprintf(tt('%d person likes this', '%d people like this', $likes), $likes),

        '$dislike' => $dislikes,
        '$strdislike' => sprintf(tt("%d person doesn't like this", "%d people don't like this", $dislikes), $dislikes),

        '$baseurl' => $a->get_baseurl(),
    ));

    return $o;
}
