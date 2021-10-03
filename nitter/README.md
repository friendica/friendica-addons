nitter Addon for Friendica
==========================

This addon will replace all occurances of the string _https://twitter.com_ with the server address of a nitter installation in all displayed postings on a Friendica node.

Note: If you are using the twitter connector on your server, the links to the contacts profile pages will not be replaced by this addon. Only links in the body of the postings are affected.

Nitter sources can be found on [github.com](https://github.com/zedeus/nitter) it is released unter the AGPLv3 or later.

Why
---

Excerp from nitters about page.

> It's basically impossible to use Twitter without JavaScript enabled. If you try, you're redirected to the legacy mobile version which is awful both functionally and aesthetically. For privacy-minded folks, preventing JavaScript analytics and potential IP-based tracking is important, but apart from using the legacy mobile version and a VPN, it's impossible.
>
> Using an instance of Nitter (hosted on a VPS for example), you can browse Twitter without JavaScript while retaining your privacy. In addition to respecting your privacy, Nitter is on average around 15 times lighter than Twitter, and in some cases serves pages faster.
>
> In the future a simple account system will be added that lets you follow Twitter users, allowing you to have a clean chronological timeline without needing a Twitter account.

Changelog
---------

* **Version 2.0**
  * Changes the used hook by the addon, so that attached previews of postings get replaced as well.
    This means the admins need to reload the addon
* **Version 1.1**
  * Initial localization support with DE translation
  * Configurable nitter instance address from the admin panel
* **Version 1.0**: Initial Release
