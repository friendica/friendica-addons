MathJax Addon
=============

* Author: Tobias Diekershoff
* License: [3-clause BSD](http://opensource.org/licenses/BSD-3-Clause) license
  (see the LICENSE file in the addon directory)

About
-----

This addon for friendica includes the [MathJax][1] CDN to enable rendering of
[LaTeX][2] formulae in your friendica postings.

Configuration
-------------
All you need to do is provide Friendica with the base URL of MathJax. This can
be either the URL of the CDN of MathJax or your own installation.

In case you want to use the CDN you can try the following URL as a quick start

	http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_HTML

In case you don't want or can use the admin panel of Friendica you can activate
the addon by adding _mathjax_ to the list in your config/local.ini.php file

	[system]
	addon = ...,mathjax

and then providing the base URL after that in the config/addon.ini.php file

	[mathjax]
	baseurl = [the URL to your MathJax installation];

Usage
=====

Once the addon is configured you can use LaTeX syntax in your postings to share
formulae with your contacts. But remember that the formulae are rendered in the
browser of the user thus your contacts need to activate this addon as well. If
they don't they will only see the LaTeX syntax in your texts.

Just enclose your equations in $$...$$ pairs like e.g. $$f_c(x)=ax+b$$.

[1]: http://www.mathjax.org/
[2]: https://en.wikipedia.org/wiki/LaTeX
