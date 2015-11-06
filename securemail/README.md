Secure Mail
-----------

Send notification mails to user encrypted with GPG.
Each user can enable it and submit his public key under Settings-> Addon
-> "Secure Mail" Settings.

Use 'php-gpg' library, a pure PHP implementation of GPG/PGP, released
under GPL. See [project repo](https://github.com/jasonhinkle/php-gpg).

This plugin could have some problems with keys larger than 2048 ([see issue](https://github.com/jasonhinkle/php-gpg/issues/7))

Need Friendica version > 3.3.2 to work.
