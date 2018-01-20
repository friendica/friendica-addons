# Calendar with CalDAV Support

**THIS ADDON IS UNSUPPORTED**

This is a rewrite of the calendar system used by the german social network [Animexx](http://www.animexx.de/).
It's still in a very early stage, so expect major bugs. Please feel free to report any of them, by mail (cato@animexx.de) or Friendica: http://friendica.hoessl.eu/profile/cato

At the moment, the calendar system supports the following features:
- A web-based drag&drop interface for managing events
- All-Day-Events, Multi-Day-Events, and time-based events
- Giving the subject, a description, a location and a color for the event (the color is not available through CalDAV, though)
- Recurrences (not the whole set of options given in the iCalendar spec, but the most important ones)
- Notification by e-mail. Multiple notifications can be set per event
- Multiple calendars per user
- Access to the events using CalDAV (using iPhone, Thunderbird Lightning etc., see below)
- Read-only access to the friendica-native events (also using CalDAV)
- The friendica-contacts are made available using CardDAV (confirmed to work with iOS)
- The events of a calendar can be exported as ICS file. ICS files can be imported into a calendar


## Internationalization:
- At the moment, settings for the US and the german systems are selectable (regarding the date format and the first day of the week). More will be added on request.
- The basic design of the system is aware of timezones; however this is not reflected in the UI yet. It currently assumes that the timezone set in the friendica-installation matches the user's local time and matches the local time set in the user's operating system.

## CalDAV device compatibility:
- iOS (iPhone/iPodTouch) works
- Thunderbird Lightning works
- Android:
  - aCal (http://andrew.mcmillan.net.nz/projects/aCal) works, available in F-Droid and Google Play
  - CalDAV-Sync (http://dmfs.org/caldav/) works, non-free

## Installation
After activating, serveral tables in the database have to be created. The admin-interface of the addon will try to do this automatically.
In case of errors, the SQL-statement to create the tables manually are shown in the admin-interface.


## Functuality missing: (a.k.a. "Roadmap")
- Sharing events; all events are private at the moment, therefore this system is not a complete replacement for the friendica-native events
- Attendees / Collaboration


## Used libraries

SabreDAV
http://code.google.com/p/sabredav/
New BSD License

wdCalendar
http://www.web-delicious.com/jquery-plugins/
GNU Lesser General Public License

jQueryUI
http://jqueryui.com/
Dual-licenced: MIT and GPL licenses

TimePicker
http://www.texotela.co.uk/code/jquery/timepicker/
Dual-licenced: MIT and GPL licenses

ColorPicker
http://laktek.com/2008/10/27/really-simple-color-picker-in-jquery/
MIT License



Author of this addon (the parts that are not part of the libraries above):
Tobias Hößl
http://friendica.hoessl.eu/profile/cato
http://www.hoessl.eu/
tobias@hoessl.eu
@TobiasHoessl

Originally developed for:
 Animexx e.V. / http://www.animexx.de/
