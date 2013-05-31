StatusNet Connector
===================
Main authors Tobias Diekershoff and Michael Vogel.

With this addon to friendica you can give your user the possibility to post
their public messages to any StatusNet instance.  The messages will be strapped
their rich context and shortened to to the character limit of the StatusNet
instance in question if necessary. If shortening of the message was performed a
link will be added to the notice pointing to the original message on your
server.

Requirements
------------

Due to the distributed nature of the StatusNet network, each user who wishes to
forward public messages to a StatusNet account has to get the OAuth credentials
for themselves, which makes this addon a little bit more user unfriendly than
the Twitter Plugin is. Nothing too geeky though!

The inclusion of a shorturl for the original posting in cases when the message
was longer than the maximal allowed notice length requires it, that you have
PHP5+ and curl on your server.
Where to find

In the friendica addon git repository /statusnet/, this directory contains all
required PHP files (including the [Twitter OAuth library] [1] by Abraham Williams,
MIT licensed and the [Slinky library] [2] by Beau Lebens, BSD license), a CSS file
for styling of the user configuration and an image to Sign in with StatusNet.

[1]:https://github.com/abraham/twitteroauth
[2]:http://dentedreality.com.au/projects/slinky

Configuration
=============

Global Configuration
--------------------

**If you have configured an admin account, you can configure this plugin from
the admin panel.** First activate it from the plugin section of the panel.
Afterwards you will have a separate configuration page for the plugin, where
you can provide a set of globally available OAuth credentials for different
StatusNet pages which will be available for all users of your server.

If you don't use the admin panel, you can configure the relay using the
.htconfig.php file of your friendica installation. To activate the relay add
it's name to the list of activated addons.

    $a->config['system']['addon'] = "statusnet, ..."

If you want to provide preconfigured StatusNet instances for your user add the
credentials for them by adding

    $a->config['statusnet']['sites'] = array (
       array ('sitename' => 'identi.ca', 'apiurl' => 'https://identi.ca/api/',
	 'consumersecret' => 'OAuth Consumer Secret here', 'consumerkey' => 'OAuth
	 Consumer Key here'),
       array ('sitename' => 'Some other Server', 'apiurl' =>
	 'http://status.example.com/api/', 'consumersecret'  => 'OAuth
	 Consumer Secret here', 'consumerkey' => 'OAuth Consumer Key here')
    );

to the config file.

Regardless of providing global OAuth credentials for your users or not, they
can always add their own OAuth-Key and -Secret thus enable the relay for any
StatusNet instance they may have an account at.

User Configuration 
------------------

When the addon is activated the user has to acquire three things in order to
connect to the StatusNet account of choice.

* the base URL for the StatusNet API, for identi.ca this was https://identi.ca/api/
* OAuth Consumer key & secret

To get the OAuth Consumer key pair the user has to (a) ask her Friendica admin
if a pair already exists or (b) has to register the Friendica server as a
client application on the StatusNet server. This can be done from the account
settings under "Connect -> Connections -> Register an OAuth client application
-> Register a new application".

During the registration of the OAuth client remember the following:

* there is no callback URL
* register a desktop client
* with read & write access
* the Source URL should be the URL of your friendica server

After the required credentials for the application are stored in the
configuration you have to actually connect your friendica account with
StatusNet. To do so follow the Sign in with StatusNet button, allow the access
and copy the security code into the plugin configuration. Friendica will then
try to acquire the final OAuth credentials from the API, if successful the
plugin settings will allow you to select to post your public messages to your
StatusNet account.

Mirroring of Public Postings
----------------------------

To avoid endless loops of public postings that are send to StatusNet and then
mirrored back into your friendica stream you have to set the _name of the
application you registered there_ of your friendica node is using to post to
StatusNet in the .htconfig.php file.

    $a->config['statusnet']['application_name'] = "yourname here";
 
Connector Options for the User
==============================

* **Allow posting to StatusNet** If you want your _public postings_ being
  optionally posted to your associated StatusNet account as well, you need to
  check this box.
* **Send public postings to StatusNet by default** if you want to have _all_
  your public postings being send to your StatusNet account you need to check
  this button as well. Otherwise you have to enable the relay of your postings
  in the ACL dialog (click the lock button) before posting an entry.
* **Mirror all posts from statusnet that are no replies or repeated messages**
  if you want your postings from StatusNet also appear in your friendica
  postings, check this box. Replies to other people postings, repostings and your own
  postings that were send from friendica wont be mirrored into your friendica
  stream.
* **Shortening method that optimizes the post** by default friendica checks how
  many characters your StatusNet instance allows you to use for a posting and
  if a posting is longer then this amount of characters it will shorten the
  message posted on StatusNet and add a short link back to the original
  posting. Optionally you can check this box to have the shortening of the
  message use an optimization algorithm. _TODO add infos how this is
  optimized_
* **Send linked #-tags and @-names to StatusNet** if you want your #-tags and
  @-mentions linked to the friendica network, check this box. If you want to
  have StatusNet handle these things for the relayed end of the posting chain,
  uncheck it.
* **Clear OAuth configuration** if you want to remove the currently associated
  StatusNet account from your friendica account you have to check this box and
  then hit the submit button. The saved settings will be deleted and you have
  to reconfigure the StatusNet connector to be able to relay your public
  postings to a StatusNet account.

License
=======

The _StatusNet Connector_ is licensed under the [3-clause BSD license][3] see the
LICENSE file in the addons directory.

[3]: http://opensource.org/licenses/BSD-3-Clause
