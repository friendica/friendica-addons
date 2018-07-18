# Libravatar Addon
by [Klaus Weidenbach](http://friendica.dszdw.net/profile/klaus)

**Please note that [the Libravatar service will shut down on 2018-09-01:](https://blog.libravatar.org/posts/Libravatar.org_is_shutting_down_on_2018-09-01/).**
You maybe want to switch to the catavatar or gravatar addon.

This addon allows you to look up an avatar image for new users and contacts at [Libravatar](http://www.libravatar.com). It will be used if there have not been found any other avatar images yet for example through OpenID.

Libravatar is a free and open replacement for Gravatar. It is a service where people can store an avatar image for their email-addresses. These avatar images can get looked up for example in comment functions, profile pages, etc. on other sites. There exists a central installation at [www.libravatar.com](http://www.libravatar.com), but you can also host it on your own server. If no avatar was found Libravatar will look up at Gravatar as a fallback.
There is no rating available, as it is on Gravatar, so all avatar lookups are g-rated. (Suitable for all audiences.)

You can not use the Libravatar and Gravatar addon at the same time. You need to choose one. If you need other ratings than g you better stay with Gravatar, otherwise it is safe to use Libravatar, because it will fall back to Gravatar if nothing was found at Libravatar.

* * *

# Configuration
## Default Avatar Image
If no avatar was found for an email Libravatar can create some pseudo-random generated avatars based on an email hash. You can choose between these presets:

* __MM__: (mystery-man) a static image
* __Identicon__: a generated geometric pattern based on email hash
* __Monsterid__: a generated 'monster' with different colors, faces, etc. based on email hash
* __Wavatar__: faces with different features and backgrounds based on email hash
* __Retro__: 8-bit arcade-styled pixelated faces based on email hash

See examples at [Libravatar][1].

## Alternative Configuration
Open the config/local.ini.php file and add "libravatar" to the list of activated addons:

        [system]
		addon = ...,libravatar

You can add one configuration variables for the addon to the config/addon.ini.php file:

        [libravatar]
		default_avatar = identicon

[1]: http://wiki.libravatar.org/api/ "See API documentation at Libravatar for more information"
