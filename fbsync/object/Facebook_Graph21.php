<?php

require_once("./addon/fbsync/object/Facebook.php");

Class Facebook_Graph21 extends Facebook
{
    public $access_token;// = "test";
    public $uid;
    private $cachedContacts = array();
    public $graphBase = "https://graph.facebook.com/v2.1/";
    
    function __construct($uid)
    {
        $this->uid = $uid;
        $this->access_token = get_pconfig($uid,'facebook','access_token');
    }
    
    function PictureURL($facebookID)
    {
        //Picture is always here.  This url redirects to CDN.  CDN images should not be used as they can move around.
        //TODO: the Proxy URL that is being used to serve images is screwing up this URL
        //TODO: Add this to make image sized correctly '&type=square&width=80&height=80'
        return $this->graphBase . $post->from->id . '/picture';
    }
    
    //Every User Request must be processed individually.
    //Facebook no longer allows you to request all of a users contacts.
    function FetchContact($facebookID, $create_user)
    {
        /*
            $facebookID     - The facebook user to fetch
            $create_user    - If the fetched user doesn't exist, create him as a contact.
        */
        
        //TODO: check if the contact has been updated recently before making this hit.  Not sure if this is possible.
        $url = $this->graphBase . $facebookID . '?access_token=' . $this->access_token;
        $contact = fetch_url($url);
        $contact = json_decode($contact);
        $url = normalise_link($contact->link);
        
        // Check if the unique contact is existing
        $r = q("SELECT id FROM unique_contacts WHERE url='%s' LIMIT 1",
            dbesc($url));

        if (count($r) == 0)
            q("INSERT INTO unique_contacts (url, name, nick, avatar) VALUES ('%s', '%s', '%s', '%s')",
                dbesc($url),
                dbesc($contact->name),
                dbesc($contact->username),
                dbesc($this->PictureURL($contact->id)));
        else
            q("UPDATE unique_contacts SET name = '%s', nick = '%s', avatar = '%s' WHERE url = '%s'",
                dbesc($contact->name),
                dbesc($contact->username),
                dbesc($this->PictureURL($contact-id)),
                dbesc($url));

        $r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
            intval($this->uid), dbesc("facebook::".$contact->id));
        
        if(!count($r) AND !$create_user)
            return(0);

        if (count($r) AND ($r[0]["readonly"] OR $r[0]["blocked"])) {
            logger("fbsync_fetch_contact: Contact '".$r[0]["nick"]."' is blocked or readonly.", LOGGER_DEBUG);
            return(-1);
        }
        //This should probably happen always if the unique_contacts record is created.
        if(!count($r)) 
        {
            // create contact record if it doesn't exist
            q(" INSERT INTO `contact` (
                    `uid`, 
                    `created`, 
                    `alias`,
                    `poll`, 
                    `network`, 
                    `rel`, 
                    `priority`,
                    `writable`, 
                    `blocked`, 
                    `readonly`, 
                    `pending`
                ) VALUES ( %d, '%s', '%s', '%s', '%s', %d, %d, %d, %d, %d, %d)",
                intval($this->uid),                       //uid
                dbesc(datetime_convert()),          //created
                dbesc("facebook::".$contact->id),   //alias                
                dbesc("facebook::".$contact->id),   //poll
                dbesc(NETWORK_FACEBOOK),            //network
                intval(CONTACT_IS_FRIEND),          //rel
                intval(1),                          //priority
                intval(1),                          //writable
                intval(0),                          //blocked
                intval(0),                          //readonly
                intval(0)                           //pending
            );

            $r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d LIMIT 1",
                dbesc("facebook::".$contact->id),
                intval($this->uid)
                );
        }       
                
		// update profile photos once every 12 hours as we have no notification of when they change.
        //TODO: Probably need to test if avatar-date is null
		$update_photo = ($r[0]['avatar-date'] < datetime_convert('','','now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion
		if((! $r[0]['photo']) || (! $r[0]['thumb']) || (! $r[0]['micro']) || ($update_photo)) {

			logger("fbsync_fetch_contact: Updating contact ".$contact->username, LOGGER_DEBUG);

			require_once("Photo.php");

			$photos = import_profile_photo($this->PictureURL($facebookID), $this->uid, $r[0]['id']);

			q("UPDATE `contact` SET 
                        `photo` = '%s',
						`thumb` = '%s',
						`micro` = '%s',
						`name-date` = '%s',
						`uri-date` = '%s',
						`avatar-date` = '%s',
						`url` = '%s',
						`nurl` = '%s',
						`addr` = '%s',
						`name` = '%s',
						`nick` = '%s',
						`notify` = '%s'
					WHERE `id` = %d",
				dbesc($photos[0]),
				dbesc($photos[1]),
				dbesc($photos[2]),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc($contact->url),
				dbesc(normalise_link($contact->url)),
				dbesc($contact->username."@facebook.com"),
				dbesc($contact->name),
				dbesc($contact->username),
				dbesc($contact->id),
				intval($r[0]['id'])
			);
		}
        
        return($r[0]["id"]);
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
        {
            logger('fbsync_createpost: skipping post $post->id--already exists', LOGGER_DEBUG);
            return;
        }
        
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
        
        
        $postarray['author-avatar'] = $this->PictureURL($post->from->id);
        
        //TODO: Source not in in graph api.  What was this before?  Seemed like it was the same as the author with FQL
        //$postarray['owner-name'] = $contacts[$post->source_id]->name;
        //$postarray['owner-link'] = $contacts[$post->source_id]->url;
        //$postarray['owner-avatar'] = $contacts[$post->source_id]->pic_square;

        //TODO: Parent Post Code
        
        //TODO: Set $postarray['contact-id'] = $contact_id;  Should either be the actor_id or the source_id (not in graph?)
        //echo $post->from->id;
        //TODO: From Needs to be added in order to be set for item?
        $postarray['contact-id'] = $this->FetchContact($post->from->id, $create_user);
        
        //Set Object Type
        //TODO: This code is broken.
        if (!isset($post->attachment[0]->type))  //This is never set since its from the FQL dataset.
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
        /*
        echo "type: " . $postarray['object-type'];
        die();
        */
        //TODO: Body needs more testing, and has some more fringe cases.
        $postarray["body"] = $this->AssembleBody($post->name, $post->link, $post->description, $post->picture, $postarray['object-type']);
        
        
        
        //TODO: Do tags
        $postarray["tag"] = "This is the tag";
        
        $postarray['app'] = ($post->application->name == "Links" ? "Facebook" : $post->application->name);
        
        if(isset($post->privacy) && $post->privacy->value !== '') {
            $postarray['private'] = 1;
            $postarray['allow_cid'] = '<' . $uid . '>';
        }
    
        $item = item_store($postarray);
        logger('fbsync_createpost: User ' . $uid . ' posted feed item '.$item, LOGGER_DEBUG);
        
        return $postarray;
    }
    
    function AssembleBody($Title, $Href, $Body, $Picture, $ObjectType)
    {
        /*
        $postarray["body"] = (isset($post->message) ? escape_tags($post->message) : '');

        $msgdata = fbsync_convertmsg($a, $postarray["body"]);

        $postarray["body"] = $msgdata["body"];
        $postarray["tag"] = $msgdata["tags"];
        */
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
        */
        
        $content = '[class="type-'. $ObjectType . '"]' . $content . '[/class]';
        
        /*
        TODO: 
        * What is the wall-to-wall system setting supposed to do?  What is the "Share" syntax used for?
        This code does not seem to match what happens with the previous posts.  I stripped out some stuff comparing author and source below.
        
        if (!intval(get_config('system','wall-to-wall_share'))) {
            $ShareAuthor = "Test Share Author";
            $ShareAuthorLink = "http://test.com";
            
            $content = '[share author="' . $ShareAuthor . '" profile="' . $ShareAuthorLink . '" avatar="' .  $ShareAuthorAvatar . '"]' . $content . '[/share]';
        }
        */
        
        return $content;
    }
}

?>