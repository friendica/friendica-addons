<?php


class Sabre_CalDAV_Calendar_Private extends Sabre_CalDAV_Calendar
{

	public function getACL()
	{

		return array(
			array(
				'privilege' => '{DAV:}read',
				'principal' => $this->calendarInfo['principaluri'],
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}write',
				'principal' => $this->calendarInfo['principaluri'],
				'protected' => true,
			),
			/*
			array(
				'privilege' => '{DAV:}read',
				'principal' => $this->calendarInfo['principaluri'] . '/calendar-proxy-write',
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}write',
				'principal' => $this->calendarInfo['principaluri'] . '/calendar-proxy-write',
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}read',
				'principal' => $this->calendarInfo['principaluri'] . '/calendar-proxy-read',
				'protected' => true,
			),
			array(
				'privilege' => '{' . Sabre_CalDAV_Plugin::NS_CALDAV . '}read-free-busy',
				'principal' => '{DAV:}authenticated',
				'protected' => true,
			),
			*/

		);

	}



}