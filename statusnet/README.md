GNU Social Connector
===================
Main authors Tobias Diekershoff and Michael Vogel.

With this addon to friendica you can give your users the possibility to post their *public* messages to GNU Social 
and to import their timeline of their legacy GNU Social accounts. The messages will be strapped their rich context 
and shortened if necessary.

Requirements
------------

Due to the distributed nature of the GNU Social network, each user who wishes to
forward public messages to a GNU Social account has to get the OAuth credentials
for themselves, which makes this addon a little bit more user unfriendly than
the Twitter Plugin is. Nothing too geeky though!

The inclusion of a shorturl for the original posting in cases when the message
was longer than the maximal allowed notice length requires it, that you have
PHP5+ and curl on your server.

Where to find
-------------

In the friendica addon git repository /statusnet/, this directory contains all
required PHP files (including the [Twitter OAuth library] [1] by Abraham Williams,
MIT licensed and the [Slinky library] [2] by Beau Lebens, BSD license), a CSS file
for styling of the user configuration and an image to Sign in with GNU Social.

[1]:https://github.com/abraham/twitteroauth
[2]:http://dentedreality.com.au/projects/slinky

Configuration
=============

User Configuration 
------------------

When the addon is activated the user has to acquire three things in order to
connect to the GNU Social account of choice.

* the base URL for the GNU Social API, for identi.ca this was https://identi.ca/api/
* OAuth Consumer key & secret

To get the OAuth Consumer key pair the user has to (a) ask her Friendica admin
if a pair already exists or (b) has to register the Friendica server as a
client application on the GNU Social server. This can be done from the account
settings under "Connect -> Connections -> Register an OAuth client application
-> Register a new application".

During the registration of the OAuth client remember the following:

* there is no callback URL
* register a desktop client
* with read & write access
* the Source URL should be the URL of your friendica server

After the required credentials for the application are stored in the
configuration you have to actually connect your friendica account with
GNU Social. To do so follow the Sign in with GNU Social button, allow the access
and copy the security code into the plugin configuration. Friendica will then
try to acquire the final OAuth credentials from the API, if successful the
plugin settings will allow you to select to post your public messages to your
GNU Social account.

License
=======

The _GNU Social Connector_ is licensed under the [3-clause BSD license][3] see the
LICENSE file in the addons directory.

[3]: http://opensource.org/licenses/BSD-3-Clause
