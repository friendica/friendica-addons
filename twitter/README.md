Twitter Plugin
==============

Main authors Tobias Diekershoff and Michael Vogel.

With this addon to friendica you can give your user the possibility to post
their *public* messages to Twitter. The messages will be strapped their rich
context and shortened to 140 characters length if necessary. If shortening of
the message was performed a link will be added to the Tweet pointing to the
original message on your server.

The addon can also mirror a users Tweets into the ~friendica wall.

There is a similar addon for forwarding public messages to
[StatusNet](http://status.net).

Requirements
------------

To use this plugin you have to register an application for your friendica
instance on Twitter with
* read and write access
* don't set a callback URL
* we do not intend to use Twitter for login
The registration can be done at twitter.com/apps and you need a Twitter
account for doing so.

After you registered the application you get an OAuth consumer key / secret
pair that identifies your app, you will need them for configuration.

The inclusion of a shorturl for the original posting in cases when the
message was longer than 140 characters requires it, that you have *PHP5+* and
*curl* on your server.

Where to find
-------------

In the friendica addon git repository /twitter/, this directory contains
all required PHP files (including the [Twitter OAuth library][1] by Abraham
Williams, MIT licensed and the [Slinky library][2] by Beau Lebens, BSD license),
a CSS file for styling of the user configuration and an image to _Sign in with
Twitter_.

[1]: https://github.com/abraham/twitteroauth
[2]: http://dentedreality.com.au/projects/slinky/

Configuration
=============

Global Configuration
--------------------

If you enabled an administrator account, please use the admin panel to configure
the Twitter relay. If you for any reason prefer to use a configuration file instead 
of the admin panels, please refer to the Alternative Configuration below. 

Activate the plugin from the plugins section of your admin panel.  When you have
done so, add your consumer key and consumer secret in the settings section of the 
plugin page.

When this is done your user can now configure their Twitter connection at
"Settings -> Connector Settings" and enable the forwarding of their *public*
messages to Twitter.

Alternative Configuration
-------------------------

To activate this addon add twitter to the list of active addons in your
.htconfig.php file 

    $a->config['system']['addon'] = "twitter, ..."

Afterwards you need to add your OAuth consumer key / secret pair to it by
adding the following two lines

    $a->config['twitter']['consumerkey'] = 'your consumer KEY here';
    $a->config['twitter']['consumersecret'] = 'your consumer SECRET here';


Mirroring of Public Postings
----------------------------

To avoid endless loops of public postings that are send to Twitter and then
mirrored back into your friendica stream you have to set the _name of the
application you registered there_ of your friendica node is using to post to
Twitter in the .htconfig.php file.

    $a->config['twitter']['application_name'] = "yourname here";
 
Connector Options for the User
==============================

When the OAuth consumer informations are correctly placed into the
configuration file and a user visits the "Connector Settings" page they can now
connect to Twitter. To do so one has to follow the _Sign in with Twitter_
button (the page will be opened in a new browser window/tab) and get a PIN from
Twitter. This PIN has to be entered on the settings page. After submitting the
PIN the plugin will get OAuth credentials identifying this user from the
friendica account.

After this step was successful the user now has the following config options.

* **Allow posting to StatusNet** If you want your _public postings_ being
  optionally posted to your associated Twitter account as well, you need to
  check this box.
* **Send public postings to StatusNet by default** if you want to have _all_
  your public postings being send to your Twitter account you need to check
  this button as well. Otherwise you have to enable the relay of your postings
  in the ACL dialog (click the lock button) before posting an entry.
* **Mirror all posts from statusnet that are no replies or repeated messages**
  if you want your postings from Twitter also appear in your friendica
  postings, check this box. Replies to other people postings, repostings and your own
  postings that were send from friendica wont be mirrored into your friendica
  stream.
* **Shortening method that optimizes the post** by default friendica checks how
  many characters your Twitter instance allows you to use for a posting and
  if a posting is longer then this amount of characters it will shorten the
  message posted on Twitter and add a short link back to the original
  posting. Optionally you can check this box to have the shortening of the
  message use an optimization algorithm. _TODO add infos how this is
  optimized_
* **Send linked #-tags and @-names to StatusNet** if you want your #-tags and
  @-mentions linked to the friendica network, check this box. If you want to
  have Twitter handle these things for the relayed end of the posting chain,
  uncheck it.
* **Clear OAuth configuration** if you want to remove the currently associated
  Twitter account from your friendica account you have to check this box and
  then hit the submit button. The saved settings will be deleted and you have
  to reconfigure the Twitter connector to be able to relay your public
  postings to a Twitter account.

License
=======

The _StatusNet Connector_ is licensed under the [3-clause BSD license][3] see the
LICENSE file in the addons directory.

[3]: http://opensource.org/licenses/BSD-3-Clause


