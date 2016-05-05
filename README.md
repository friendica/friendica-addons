# frio-hovercard
friendica hovercard addon for the frio theme

This addon provides a hovercard for firendicas frio theme.
**It not intended to use the plugin without the frio theme or vice versa.

It's a sample implementation how friendica could provide json userdata for
the hovercard. Ideally we should implement something like a profile data api endpoint like 
it is done in diaspora & gnu social. This would search at the foreign server for the requested data.
We could also think about supporting requesting the api of diaspora  and gnu social.

At the present state the data comes from the own contact and gcontact table
