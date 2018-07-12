Twitter Addon
==============

Main authors Tobias Diekershoff, Michael Vogel and Hypolite Petovan.

This bi-directional connector addon allows each user to crosspost their Friendica public posts to Twitter, import their Twitter timeline, interact with tweets from Friendica, and crosspost to Friendica their public tweets.

## Installation

To use this addon you have to register an [application](https://apps.twitter.com/) for your Friendica instance on Twitter.
Register your Friendica site as "Client" application with "Read & Write" access we do not need "Twitter as login".
Please leave the field "Callback URL" empty.
When you've registered the app you get the OAuth Consumer key and secret pair for your application/site.

After the registration please enter the values for "Consumer Key" and "Consumer Secret" in the [administration](admin/addons/twitter).

## Alternative configuration

Add your key pair to your global config/addon.ini.php.

    [twitter]
    consumerkey = your consumer_key here
    consumersecret = your consumer_secret here

To activate the addon itself add it to the [system] addon setting.
After this, users can configure their Twitter account settings from "Settings -> Addon Settings".

## License

The _Twitter Connector_ is licensed under the [3-clause BSD license][2] see the LICENSE file in the addons directory.

The _Twitter Connector_ uses the [Twitter OAuth library][2] by Abraham Williams, MIT licensed

[1]: http://opensource.org/licenses/BSD-3-Clause
[2]: https://github.com/abraham/twitteroauth
