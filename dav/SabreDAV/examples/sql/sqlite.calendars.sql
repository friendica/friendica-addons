CREATE TABLE calendarobjects (
	id integer primary key asc,
    calendardata blob,
    uri text,
    calendarid integer,
    lastmodified integer,
    etag text,
    size integer,
    componenttype text,
    firstoccurence integer,
    lastoccurence integer
);

CREATE TABLE calendars (
    id integer primary key asc,
    principaluri text,
    displayname text,
    uri text,
	ctag integer,
    description text,
	calendarorder integer,
    calendarcolor text,
	timezone text,
	components text
);
