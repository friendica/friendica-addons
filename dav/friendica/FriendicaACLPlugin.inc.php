<?php

class Sabre_DAVACL_Plugin_Friendica extends Sabre_DAVACL_Plugin {

	/*
	 * A dirty hack to make iOS CalDAV work with subdirectorys.
	 * When using a Root URL like /dav/ (as it is necessary for friendica), iOS does not evaluate the current-user-principal property,
	 * but only principal-URL. Actually principal-URL is not allowed in /dav/, only for Principal collections, but this seems
	 * to be the only way to force iOS to look at the right location.
	 */

	public function beforeGetProperties($uri, Sabre_DAV_INode $node, &$requestedProperties, &$returnedProperties) {

		parent::beforeGetProperties($uri, $node, $requestedProperties, $returnedProperties);

		if (false !== ($index = array_search('{DAV:}principal-URL', $requestedProperties))) {

			unset($requestedProperties[$index]);
			$returnedProperties[200]['{DAV:}principal-URL'] = new Sabre_DAV_Property_Href('principals/users/' . strtolower($_SERVER["PHP_AUTH_USER"]) . '/');

		}
		if (false !== ($index = array_search('{urn:ietf:params:xml:ns:caldav}calendar-home-set', $requestedProperties))) {

			unset($requestedProperties[$index]);
			$returnedProperties[200]['{urn:ietf:params:xml:ns:caldav}calendar-home-set'] = new Sabre_DAV_Property_Href('calendars/' . strtolower($_SERVER["PHP_AUTH_USER"]) . '/');

		}

	}

}