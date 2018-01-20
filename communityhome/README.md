Community Home
--------------

This addon overwrites the default home page shown to not logged users.
On sidebar there are the login form, last ten users (if they have 
choosed to be in site directory), last ten public photos and last ten
"likes" sent by a site user or about a site user's item

In main content is shown the community stream. This addon doesn't 
honour your community page visibility site setting: the community 
stream is shown also if you have choose to not show the community page.

If 'home.html' is found in your friendica root, its content is inserted 
before community stream

Each elements can be show or not. At the moment, there is no admin page
for settings, so this settings must be added to yout .htconfig.php


    $a->config['communityhome']['showcommunitystream'] = true;
    $a->config['communityhome']['showlastlike'] = true;
    $a->config['communityhome']['showlastphotos'] = true;
    $a->config['communityhome']['showactiveusers'] = true;
    $a->config['communityhome']['showlastusers'] = true;

If you don't want to show something, set it to false.

Note:
-----

- Default is "false". With no settings in .htconfig.php, nothing is 
shown, except login form and content of 'home.html'

- Active users query can be heavy for db, and on some system don't work
