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
        $postarray['contact-id'] = 1;
        
        //Set Object Type
        if (!isset($post->attachment[0]->type))
        {
            //Default Object Type
            $postarray['object-type'] = ACTIVITY_OBJ_NOTE; // default value - is maybe changed later when media is attached
        } else {
            // Change the object type when an attachment is present
            $postarray['object-type'] = $post->attachment[0]->type; // default value - is maybe changed later when media is attached
        }
        /* More type code.  I think this is not necessary.
        require_once("include/oembed.php");
        $oembed_data = oembed_fetch_url($post->attachment->href);
        $type = $oembed_data->type;
        if ($type == "rich")
            $type = "link";
        */
        
        //TODO: Body needs more testing, and has some more fringe cases.
        $postarray["body"] = $this->AssembleBody($post->name, $post->link, $post->description, $post->picture); //"This is the body. [quote]This is the quote.[/quote]";
        
        //TODO: Do tags
        $postarray["tag"] = "This is the tag";
        
        $postarray['app'] = ($post->application->name == "" ? "Facebook" : $post->application->name);
        
        if(isset($post->privacy) && $post->privacy->value !== '') {
            $postarray['private'] = 1;
            $postarray['allow_cid'] = '<' . $uid . '>';
        }
    
        $item = item_store($postarray);
        logger('fbsync_createpost: User ' . $uid . ' posted feed item '.$item, LOGGER_DEBUG);
        
        return $postarray;
    }
    
    function AssembleBody($Title, $Href, $Body, $Picture)
    {
        //TODO: Need to do prebody code still.
        //TODO: Need to add class (aka type) code
        $postarray["body"] = (isset($post->message) ? escape_tags($post->message) : '');

        $msgdata = fbsync_convertmsg($a, $postarray["body"]);

        $postarray["body"] = $msgdata["body"];
        $postarray["tag"] = $msgdata["tags"];

        $content = "";

        if ($Picture != "")
        {
            $pictureStr = '[img]' . $Picture . '[/img]';
            if ($Href != "")
            {
                $pictureStr = '[url=' . $Href . ']' . $pictureStr .  '[/url]';
            }
            
            $content .= "\n" . $pictureStr;
        }
        
        if ($Title != "" and $Href != "") {
            
            $content .= "[bookmark=".$Href."]".$Title."[/bookmark]";

            // If a link is not only attached but also added in the body, look if it can be removed in the body.
            /*
            $removedlink = trim(str_replace($post->attachment->href, "", $postarray["body"]));

            if (($removedlink == "") OR strstr($postarray["body"], $removedlink))
                $postarray["body"] = $removedlink;
            */
        } elseif ($Title != "") {
            $content .= "[b]" . $post->attachment->name."[/b]";
        }
        
        $content .= "\n[quote]".trim($Body)."[/quote]";
        
        
        /*

        if (isset($post->attachment->media) AND (($type == "") OR ($type == "link"))) {
            foreach ($post->attachment->media AS $media) {

                if (isset($media->type))
                    $type = $media->type;

                if (isset($media->src))
                    $preview = $media->src;

                if (isset($media->photo)) {
                    if (isset($media->photo->images) AND (count($media->photo->images) > 1))
                        $preview = $media->photo->images[1]->src;

                    if (isset($media->photo->fbid)) {
                        logger('fbsync_createpost: fetching fbid '.$media->photo->fbid, LOGGER_DEBUG);
                        $url = "https://graph.facebook.com/".$media->photo->fbid."?access_token=".$access_token;
                        $feed = fetch_url($url);
                        $data = json_decode($feed);
                        if (isset($data->images)) {
                            $preview = $data->images[0]->source;
                            logger('fbsync_createpost: got fbid '.$media->photo->fbid.' image '.$preview, LOGGER_DEBUG);
                        } else
                            logger('fbsync_createpost: error fetching fbid '.$media->photo->fbid.' '.print_r($data, true), LOGGER_DEBUG);
                    }
                }

                if (isset($media->href) AND ($preview != "") AND ($media->href != ""))
                    $content .= "\n".'[url='.$media->href.'][img]'.$preview.'[/img][/url]';
                else {
                    if ($preview != "")
                        $content .= "\n".'[img]'.$preview.'[/img]';

                    // if just a link, it may be a wall photo - check
                    if (isset($post->link))
                        $content .= fbpost_get_photo($media->href);
                }
            }
        }

        if ($type == "link")
            $postarray["object-type"] = ACTIVITY_OBJ_BOOKMARK;

        if ($content)
            $postarray["body"] .= "\n";

        if ($type)
            $postarray["body"] .= "[class=type-".$type."]";

        if ($content)
            $postarray["body"] .= trim($content);

        if ($quote)
            $postarray["body"] .= "\n[quote]".trim($quote)."[/quote]";

        if ($type)
            $postarray["body"] .= "[/class]";

        $postarray["body"] = trim($postarray["body"]);

        if (trim($postarray["body"]) == "")
            return;

        if ($prebody != "")
            $postarray["body"] = $prebody.$postarray["body"]."[/share]";
        */
        
        return $content;
    }
}

?>