<?php

require_once("./addon/fbsync/object/Facebook.php");

Class Facebook_Graph21 //implements Facebook
{
    public $access_token;// = "test";
    public $uid;
    
    function __construct($uid)
    {
        $this->uid = $uid;
        $this->access_token = get_pconfig($uid,'facebook','access_token');
    }
    
    function CreatePost($a, $self, $contacts, $applications, $post, $create_user)
    {
        //Sanitize Inputs
        
        //Check if post exists
        //This is legacy--shouldn't we check if the various parts of the post was updated?
        $r = q("SELECT * FROM `item` WHERE `uid` = %d AND `uri` = '%s' LIMIT 1",
                intval($this->uid),
                dbesc('fb::'.$post->id)
            );
        if(count($r))
            return;
        
        $postarray = array();
        $postarray['gravity'] = 0;
        $postarray['uid'] = $this->uid;
        $postarray['wall'] = 0;
        
        $postarray['verb'] = ACTIVITY_POST;
        $postarray['object-type'] = ACTIVITY_OBJ_NOTE; // default value - is maybe changed later when media is attached
        $postarray['network'] =  dbesc(NETWORK_FACEBOOK);

       	$postarray['uri'] = "fb::". $post->id;
        $postarray['thr-parent'] = $postarray['uri'];
        $postarray['parent-uri'] = $postarray['uri'];
        
        //No permalink in new api.  Can use one of the action links, they seem to be all the same.
        //Another option is spliting the id AAA_BBB where AAA is the id of the person, and BBB is the ID of the post.  final url would be facebook.com/AAA/post/BBB
        $ids = split("_", $post->id);
        $postarray['plink'] = 'https://www.facebook.com/' . $ids[0] . '/posts/' . $ids[1];
        
        return $postarray;
    
    }
    
}

?>