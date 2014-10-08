<?php
require_once("boot.php");
require_once("./addon/fbsync/fbsync.php");
require_once("include/dba.php");
@include(".htconfig.php");

$a = new App();
$d = datetime_convert();
global $db;
$db = new dba($db_host, $db_user, $db_pass, $db_data, $install);


//Test Data Retrieval
//$data = fbsync_fetchfeed($a, 1);
//var_dump($data);

//Test Data Processing

// Test Base Class
require_once("./addon/fbsync/object/Facebook.php");
$myFBSync = new Facebook();

// Test graph 2.1 class
require_once("./addon/fbsync/object/Facebook_Graph21.php");
$myFBSync = new Facebook_Graph21();
$uid = 1;
$post = json_decode(readfile("./addon/fbsync/tests/graph2.1.txt"));
$myFBSync->CreatePost($a,$uid,0,0,0,$post,0);


/*
https://developers.facebook.com/tools/explorer
SELECT action_links, actor_id, app_data, app_id, attachment, attribution, comment_info, created_time, filter_key, like_info, message, message_tags, parent_post_id, permalink, place, post_id, privacy, share_count, share_info, source_id, subscribed, tagged_ids, type, updated_time, with_tags FROM stream where filter_key ='nf' ORDER BY updated_time DESC LIMIT 5


me/home?fields=actions&since=992438&updated_time=0&filter=nf&limit=1
me/home?fields=actions,link,id,created_time,application,attachments,updated_time,object_id,with_tags,comments{can_comment,comment_count},likes,message,message_tags,description,parent_id,place,privacy,shares&limit=1
https://developers.facebook.com/docs/graph-api/reference/v2.1/test-user


    "previous": "https://graph.facebook.com/v2.1/10152271780185899/home?fields=actions,link,id,created_time,application,attachments,updated_time,object_id,with_tags,comments{can_comment,comment_count},likes,message,message_tags,description,parent_id,place,privacy,shares&limit=2&since=1411141640",
    "next": "https://graph.facebook.com/v2.1/10152271780185899/home?fields=actions,link,id,created_time,application,attachments,updated_time,object_id,with_tags,comments{can_comment,comment_count},likes,message,message_tags,description,parent_id,place,privacy,shares&limit=2&until=1411141432"

    $limit = 1;
    $graph = array(        
            array(method => GET,
            "relative_url" =>"me/home?limit=$limit&fields=actions,link,id,created_time,application,attachments,updated_time,object_id,with_tags,comments{can_comment,comment_count},likes,message,message_tags,description,parent_id,place,privacy,shares&since=$last_updated"
            ),
            array(method=>GET,
                "relative_url" => "me")
        );
    
    
    static $GETRequest = '{"method":"GET","relative_url":%s}';    
    var_dump($graph);
    $graphURL = 'https://graph.facebook.com/v2.1/?batch=' . urlencode(json_encode($graph))
        .'&access_token=' . $access_token . '&method=post'
        ;
    
    //Facebook API v2.1
    $graphData = json_decode(file_get_contents($graphURL));
    
    //Facebook v2.1 Data
	$posts = json_decode($graphData[0]->body);
    
    
*/
?>