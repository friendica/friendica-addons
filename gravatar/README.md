# Gravatar Addon
by [Klaus Weidenbach](http://friendica.dszdw.net/profile/klaus)

This addon allows you to look up an avatar image for new users and contacts at [Gravatar](http://www.gravatar.com). This will be used if there have not been found any other avatar images yet for example through OpenID.

Gravatar is a popular, but centralized and proprietary service where people can store an avatar image for their email-addresses. It is widely used on many pages, for example to display an avatar for comment functions, profile pages, etc.

* * *

# Configuration
## Default Avatar Image
If no avatar was found for an email Gravatar can create some pseudo-random generated avatars based on an email hash. You can choose between these presets:

* __Gravatar__: default static Gravatar logo
* __MM__: (mystery-man) a static image
* __Identicon__: a generated geometric pattern based on email hash
* __Monsterid__: a generated 'monster' with different colors, faces, etc. based on email hash
* __Wavatar__: faces with different features and backgrounds based on email hash
* __Retro__: 8-bit arcade-styled pixelated faces based on email hash

See examples at [Gravatar][1].
## Avatar Rating
Gravatar lets users self-rate their images to be used at appropriate audiences. Choose which are appropriate for your friendica site:

* __g__: suitable for display on all wesites with any audience type
* __pg__: may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence
* __r__: may contain such things as harsh profanity, intense violence, nudity, or hard drug use
* __x__: may contain hardcore sexual imagery or extremely disurbing violence

See more information at [Gravatar][1].

## Alternative Configuration
Open the .htconfig.php file and add "gravatar" to the list of activated addons:

        $a->config['system']['addon'] = "..., gravatar";

You can add two configuration variables for the addon:

        $a->config['gravatar']['default_avatar'] = "identicon";
        $a->config['gravatar']['rating'] = "g";

[1]: http://www.gravatar.com/site/implement/images/ "See documentation at Gravatar for more information"
