Impressum Addon for Friendica
==============================

* Author: Tobias Diekershoff
* License: [3-clause BSD](http://opensource.org/licenses/BSD-3-Clause) license
  (see the LICENSE file in the addon directory)

About
-----
This addon adds an Impressum (contact) block to the /friendica page with
informations about the page operator/owner and how to contact you in case of
any questions.

In the notes and postal fields you can use bbcode tags for formatting, like in
normal friendica postings..

Configuration
-------------
Simply fill in the fields in the impressium settings page in the addons
area of your admin panel. For email adresses the "@" symbol will be obfuscated
in the source of the page to make in harder for harvesting tools.

Manual Configuration
--------------------
If you for any reason you prefer to use a configuration file instead, you can set the following variables in the config/local.ini.php file

	[impressum]
	owner =           this is the Name of the Operator
	ownerprofile =    this is an optional Friendica account where the above owner name will link to
	email =           a contact email address (optional)
					  will be displayed slightly obfuscated as name(at)example(dot)com
	postal =          should contain a postal address where you can be reached at (optional)
	notes =           additional informations that should be displayed in the Impressum block
	footer_text =     Text that will be displayed at the bottom of the pages.
