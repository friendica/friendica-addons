Twitter Addon
==============

Main authors Tobias Diekershoff and Michael Vogel.

With this addon to friendica you can give your users the possibility to post their *public* messages to Twitter and 
to import their timeline. The messages will be strapped their rich context and shortened to 280 characters length if 
necessary.

The addon can also mirror a users Tweets into the ~friendica wall.

Installation
------------

To use this addon you have to register an [application](https://apps.twitter.com/) for your friendica instance on Twitter. Please leave the field "Callback URL" empty.

After the registration please enter the values for "Consumer Key" and "Consumer Secret" in the [administration](admin/addons/twitter).

Where to find
-------------

In the friendica addon git repository /twitter/, this directory contains
all required PHP files (including the [Twitter OAuth library][1] by Abraham
Williams, MIT licensed and the [Slinky library][2] by Beau Lebens, BSD license),
a CSS file for styling of the user configuration and an image to _Sign in with
Twitter_.

[1]: https://github.com/abraham/twitteroauth
[2]: http://dentedreality.com.au/projects/slinky/

License
=======

The _StatusNet Connector_ is licensed under the [3-clause BSD license][3] see the
LICENSE file in the addons directory.

[3]: http://opensource.org/licenses/BSD-3-Clause


