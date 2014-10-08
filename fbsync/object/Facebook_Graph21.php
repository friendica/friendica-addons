<?php

require_once("./addon/fbsync/object/Facebook.php");

Class Facebook_Graph21 extends Facebook
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
        
        $postarray['created'] = datetime_convert('UTC','UTC',date("c", $post->created_time));
        $postarray['edited'] = datetime_convert('UTC','UTC',date("c", $post->updated_time));
        
       	$postarray['uri'] = "fb::". $post->id;
        $postarray['thr-parent'] = $postarray['uri'];
        $postarray['parent-uri'] = $postarray['uri'];
        
        //No permalink in new api.  Can use one of the action links, they seem to be all the same.
        //Another option is spliting the id AAA_BBB where AAA is the id of the person, and BBB is the ID of the post.  final url would be facebook.com/AAA/post/BBB
        //TODO: Remove actions if not in use
        $ids = split("_", $post->id);
        $postarray['plink'] = 'https://www.facebook.com/' . $ids[0] . '/posts/' . $ids[1];
        
        $postarray['author-name'] = $post->from->name; // $contacts[$post->actor_id]->name;
        $postarray['author-link'] = 'https://www.facebook.com/' . $post->from->id; //$contacts[$post->actor_id]->url;
        
        //TODO: Pic not included in graph
        //$postarray['author-avatar'] = $contacts[$post->actor_id]->pic_square;
        
        //TODO: Source not in in graph api.  What was this before?  Seemed like it was the same as the author with FQL
        //$postarray['owner-name'] = $contacts[$post->source_id]->name;
        //$postarray['owner-link'] = $contacts[$post->source_id]->url;
        //$postarray['owner-avatar'] = $contacts[$post->source_id]->pic_square;

        //TODO: Parent Post Code
        
        //TODO: Set $postarray['contact-id'] = $contact_id;  Should either be the actor_id or the source_id (not in graph?)
        
        //TODO: Body does not seem to be used in graph or fql any more.  What is this value supposed to be?
        $postarray["body"] = "";  
        
        //TODO: Deal with attachments
        //Kind of a big deal.
        
        $postarray['app'] = ($post->application->name == "" ? "Facebook" : $post->application->name);
        
        if(isset($post->privacy) && $post->privacy->value !== '') {
            $postarray['private'] = 1;
            $postarray['allow_cid'] = '<' . $uid . '>';
        }
    
        $item = item_store($postarray);
        logger('fbsync_createpost: User ' . $uid . ' posted feed item '.$item, LOGGER_DEBUG);
        
        return $postarray;
    }
}

?>