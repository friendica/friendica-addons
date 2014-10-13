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

//Test Data Prcoessing -- Constants-- used in both tests
$uid = 1;
$self_id = get_pconfig($uid,'fbsync','self_id');
$last_updated = get_pconfig($uid,'fbsync','last_updated');
$self = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1", intval($uid));
$user = q("SELECT * FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1", intval($uid));
$create_user = get_pconfig($uid, 'fbsync', 'create_user');

//Test Data Processing fql
$data = json_decode(file_get_contents("./addon/fbsync/tests/fql-full.txt"));
$result = fbsync_processfeed($data, $self, $a, $uid, $self_id, $user, $last_updated);

if ($result != 0) die("FQL Processing broken.\n");

echo "Done with fql_data processing.\n";

//Test data processing -- graph`

// Test Base Class
require_once("./addon/fbsync/object/Facebook.php");
$myFBSync = new Facebook();

// Test graph 2.1 class
require_once("./addon/fbsync/object/Facebook_Graph21.php");
$myFBSync = new Facebook_Graph21($uid);

//verify class loaded correctly
if ($myFBSync->uid != 1) die("class did not load");
if ($myFBSync->access_token == '') die("failed to load access_token");

//Test FetchContact 
//TODO: build tests for facebook api requests using API test user functions

//Test CreatePost
$posts = json_decode(file_get_contents("./addon/fbsync/tests/graph2.1-no-filter.txt"));

$post = $myFBSync->CreatePost($a,0,0,0,$posts->data[0],0);

//verify data
if ($post['uri'] != "fb::109524391244_10152483187826245") die("uri does not match");
if ($post['plink'] != "https://www.facebook.com/109524391244/posts/10152483187826245") die("plink does not match");

//var_dump($posts->data[0]);

//TODO: test creating the same post again


echo "All done\n";

/*
https://developers.facebook.com/tools/explorer
SELECT action_links, actor_id, app_data, app_id, attachment, attribution, comment_info, created_time, filter_key, like_info, message, message_tags, parent_post_id, permalink, place, post_id, privacy, share_count, share_info, source_id, subscribed, tagged_ids, type, updated_time, with_tags FROM stream where filter_key ='nf' ORDER BY updated_time DESC LIMIT 5

//Todo:Actions can probably be removed
me/home?fields=actions&since=992438&updated_time=0&filter=nf&limit=1
me/home?fields=actions,link,id,created_time,application,attachments,updated_time,object_id,with_tags,comments{can_comment,comment_count},likes,message,message_tags,description,parent_id,place,privacy,shares,from&limit=1
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