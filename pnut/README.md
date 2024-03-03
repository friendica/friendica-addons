# Pnut connector

With this addon to friendica you can give your users the possibility to post their *public* messages to pnut.io. 

No setup is needed for the admins to make it work for their users, however it is possible for the admin to create a client, so that the users don't have to.

To do so, go to https://pnut.io/dev and scroll down to "Create New Client".
Enter a name of your choice and enter your Friendica host name as the website.
Use https://(yourhost.name)/pnut/connect as a redirect url, replace "(yourhost.name)" with the host name of your system.
Limit the scope to "basic,files,follow,polls,presence,stream,update_profile,write_post"