Addons for Friendica
====================

This repository is a collection of addons for the [Friendica Social Communications Server](https://github.com/friendica/friendica).
You can add these addons to the /addon directory of your Friendica installation do extend the functionality of your node.

After uploading the addons to your server, you need to activate the desired addons in the Admin panel. Addons not activated have no effect on your node.

## Connectors

Among these addons there are also the [connectors](https://github.com/friendica/friendica/blob/stable/doc/Connectors.md) for various other networks (e.g. Twitter, pump.io, Google+) that are needed for communication when the protocol is not supported by Friendica core (DFRN, OStatus and Diaspora).

For communication with contacts in networks supporting those (e.g. GNU social, Diaspora and red#matrix) you just need to access the page configuration in the Admin panel and enable them. For networks where communication is only possible the API access to a remote account, you need to activate the fitting connectors.

## Development

The addon interface of Friendica is very flexible and powerful, so if you are missing functionality, your chances are high it may be added with an addon.
See the [documentation](https://github.com/friendica/friendica/blob/stable/doc/Addons.md) for more information on the addon development.

## Translation

Addons can be translated like any other part of Friendica.
Translation for addons is done at [the Transifex Friendica page](https://www.transifex.com/Friendica/friendica/dashboard/).

Read more about the workflow in the [Friendica translation documentation](https://github.com/friendica/friendica/blob/stable/doc/translations.md#addon).
