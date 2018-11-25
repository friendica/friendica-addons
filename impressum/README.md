Impressum Addon for Friendica
==============================

* Author: Tobias Diekershoff
* License: [3-clause BSD](http://opensource.org/licenses/BSD-3-Clause) license (see the LICENSE file in the addon directory)

About
-----
This addon adds an Impressum (contact) block to the /friendica page with informations about the page operator/owner and how to contact you in case of any questions.

In the notes and postal fields you can use bbcode tags for formatting, like in normal friendica postings..

Configuration
-------------
Simply fill in the fields in the impressium settings page in the addons area of your admin panel. For email adresses the "@" symbol will be obfuscated in the source of the page to make in harder for harvesting tools.

Manual Configuration
--------------------
If you for any reason you prefer to use a configuration file instead, you can set the following variables in the `config/addon.config.php` file

	'impressum' => [
        'owner' => '',           This is the Name of the Operator
        'ownerprofile' => '',    This is an optional Friendica account where the above owner name will link to
        'email' => '',           A contact email address (optional)
                                 Will be displayed slightly obfuscated as name(at)example(dot)com
        'postal' => '',          Should contain a postal address where you can be reached at (optional)
        'notes' => '',           Additional informations that should be displayed in the Impressum block
        'footer_text' => '',     Text that will be displayed at the bottom of the pages.
    ],
