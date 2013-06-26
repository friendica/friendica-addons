Piwik Plugin
============

by Tobias Diekershoff and Klaus Weidenbach

This addon allows you to embed the code necessary for the FLOSS webanalytics
tool Piwik into the Friendica pages.

Requirements
------------

To use this plugin you need a [piwik](http://piwik.org/) installation.

Where to find
-------------

In the Friendica addon git repository `/piwik/piwik.php` and a CSS file for
styling the opt-out notice.

Configuration
-------------

The easiest way to configure this addon is by activating the admin panels of
your ~friendica server and then enter the needed details on the config page
for the addon.

If you don't want to use the admin panel, you can configure the addon through
the .htconfig file.

Open the .htconfig.php file and add "piwik" to the list of activated addons.

    $a->config['system']['addon'] = "piwik, ..."

You have to add 4 more configuration variables for the addon:

    $a->config['piwik']['baseurl'] = 'example.com/piwik/';
    $a->config['piwik']['sideid'] = '1';
    $a->config['piwik']['optout'] = true;
    $a->config['piwik']['async'] = false;

Configuration fields
---------------------

* The *baseurl* points to your Piwik installation. Use the absolute path,
remember trailing slashes but ignore the protocol (http/s) part of the URL.
* Change the *sideid* parameter to whatever ID you want to use for tracking your
Friendica installation.
* The *optout* parameter (true|false) defines whether or
not a short notice about the utilization of Piwik will be displayed on every
page of your Friendica site (at the bottom of the page with some spacing to the
other content). Part of the note is a link that allows the visitor to set an
_opt-out_ cookie which will prevent visits from that user be tracked by piwik.
* The *async* parameter (true|false) defines whether or not to use asynchronous
tracking so pages load (or appear to load) faster.

Currently the optional notice states the following:

>    This website is tracked using the Piwik analytics tool. If you do not want
>    that your visits are logged this way you can set a cookie to prevent Piwik
>    from tracking further visits of the site (opt-out).

License
=======

The _Piwik addon_ is licensed under the [3-clause BSD license][3] see the
LICENSE file in the addons directory.

[3]: http://opensource.org/licenses/BSD-3-Clause
