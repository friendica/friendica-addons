<?php
require_once("Facebook.php");

Class Facebook_Graph21 implements Facebook
{
    public $access_token;
    
    function Facebook_Graph21()
    {
        $access_token = get_pconfig($uid,'facebook','access_token');
    }
    
    function CreatePost($a, $uid, $self, $contacts, $applications, $post, $create_user)
    {
        //Sanitize Inputs
        
        //Check if post exists
        //This is legacy--shouldn't we check if the various parts of the post was updated?
        $r = q("SELECT * FROM `item` WHERE `uid` = %d AND `uri` = '%s' LIMIT 1",
                intval($uid),
                dbesc('fb::'.$post->post_id)
            );
        if(count($r))
            return;
        
        $postarray = array();
        $postarray['gravity'] = 0;
        $postarray['uid'] = $uid;
        $postarray['wall'] = 0;
        
        $postarray['verb'] = ACTIVITY_POST;
        $postarray['object-type'] = ACTIVITY_OBJ_NOTE; // default value - is maybe changed later when media is attached
        $postarray['network'] =  dbesc(NETWORK_FACEBOOK);

       	$postarray['uri'] = "fb::".$post->post_id;
        $postarray['thr-parent'] = $postarray['uri'];
        $postarray['parent-uri'] = $postarray['uri'];
        $postarray['plink'] = $post->permalink;
    }
}
?>