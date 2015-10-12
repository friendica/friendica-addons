IFTTT Connector
===============

The purpose of this connector is to use [IFTTT](http://www.ifttt.com) 
to mirror posts from remote networks like Facebook.

IFTTT (IF This Then That) is a service that triggers commands on 
definable conditions.

Its main purpose is to mirror your own posts from Facebook as if they 
would have been posted directly on Friendica but it should be possible 
to mirror posts from other networks as well.

Every time when IFTTT detects that there is a new post on the remote 
network, it triggers an HTTP POST call on the Friendica server that 
creates a post from the connected account.

By now there is a posting loop detection for Facebook but not for any 
other network. So please be careful to not mirror from networks where 
you post your items via Friendica.
