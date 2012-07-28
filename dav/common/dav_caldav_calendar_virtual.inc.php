<?php

class Sabre_CalDAV_Calendar_Virtual extends Sabre_CalDAV_Calendar {

	/**
	 * Returns a list of ACE's for this node.
	 *
	 * Each ACE has the following properties:
	 *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
	 *     currently the only supported privileges
	 *   * 'principal', a url to the principal who owns the node
	 *   * 'protected' (optional), indicating that this ACE is not allowed to
	 *      be updated.
	 *
	 * @return array
	 */
	public function getACL() {

		return array(
			array(
				'privilege' => '{DAV:}read',
				'principal' => $this->calendarInfo['principaluri'],
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}read',
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

		);

	}
}
