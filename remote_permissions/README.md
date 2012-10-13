The Remote Permissions plugin enables recipients of private posts to see who else has received the post. This can be beneficial on community servers where people may want to modify the way they speak depending on who can see their comments to the post.

Note that since Friendica is federated, the local hub may have posts that originated elsewhere. In that case, the plugin has no way of knowing all the recipients of the post, and it must settle for finding out who else can see it on the local hub.

The hub admin can specify one of two behaviors for this plugin:

* **Global:** every private post on the local hub will show all recipients (or at least the ones it can discover) of the post to any other users on the local hub
* **Individual:** only private posts from those users on the local hub who "opt-in" will show the post recipients. None of the private posts that originated elsewhere will show even partial lists of post recipients
