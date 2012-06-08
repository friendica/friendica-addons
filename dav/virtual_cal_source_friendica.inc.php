<?php

class FriendicaVirtualCalSourceBackend extends VirtualCalSourceBackend
{

	/**
	 * @static
	 * @return int
	 */
	static public function getNamespace()
	{
		return CALDAV_NAMESPACE_FRIENDICA_NATIVE;
	}

	/**
	 * @static
	 * @param int $uid
	 * @param int $namespace_id
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return void
	 */
	static function createCache($uid = 0, $namespace_id = 0)
	{
	}


	static private function row2array($row, $timezone, $hostname, $uid, $namespace_id) {
		$v = new vcalendar();
		$v->setConfig('unique_id', $hostname);

		$v->setProperty('method', 'PUBLISH');
		$v->setProperty("x-wr-calname", "AnimexxCal");
		$v->setProperty("X-WR-CALDESC", "Animexx Calendar");
		$v->setProperty("X-WR-TIMEZONE", $timezone);

		if ($row["adjust"]) {
			$start = datetime_convert('UTC', date_default_timezone_get(), $row["start"]);
			$finish = datetime_convert('UTC', date_default_timezone_get(), $row["finish"]);
		} else {
			$start = $row["start"];
			$finish = $row["finish"];
		}
		$allday      = (strpos($start, "00:00:00") !== false && strpos($finish, "00:00:00") !== false);

		/*

		if ($allday) {
			$dat = Datetime::createFromFormat("Y-m-d H:i:s", $finish_tmp);
			$dat->sub(new DateInterval("P1D"));
			$finish = datetime_convert("UTC", date_default_timezone_get(), $dat->format("Y-m-d H:i:s"));
			var_dump($finish);
		}
		*/

		$subject     = substr(preg_replace("/\[[^\]]*\]/", "", $row["desc"]), 0, 100);
		$description = preg_replace("/\[[^\]]*\]/", "", $row["desc"]);

		$vevent = dav_create_vevent(wdcal_mySql2icalTime($row["start"]), wdcal_mySql2icalTime($row["finish"]), false);
		$vevent->setLocation(icalendar_sanitize_string($row["location"]));
		$vevent->setSummary(icalendar_sanitize_string($subject));
		$vevent->setDescription(icalendar_sanitize_string($description));

		$v->setComponent($vevent);
		$ical  = $v->createCalendar();
		return array(
			"uid"              => $uid,
			"namespace"        => CALDAV_NAMESPACE_FRIENDICA_NATIVE,
			"namespace_id"     => $namespace_id,
			"date"             => $row["edited"],
			"data_uri"         => "friendica-" . $namespace_id . "-" . $row["id"] . "@" . $hostname,
			"data_subject"     => $subject,
			"data_location"    => $row["location"],
			"data_description" => $description,
			"data_start"       => $start,
			"data_end"         => $finish,
			"data_allday"      => $allday,
			"data_type"        => $row["type"],
			"ical"             => $ical,
			"ical_size"        => strlen($ical),
			"ical_etag"        => md5($ical),
		);

	}

	/**
	 * @static
	 * @param int $uid
	 * @param int $namespace_id
	 * @param string|int $date_from
	 * @param string|int $date_to
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	static public function getItemsByTime($uid = 0, $namespace_id = 0, $date_from = "", $date_to = "")
	{
		$uid          = IntVal($uid);
		$namespace_id = IntVal($namespace_id);

		switch ($namespace_id) {
			case CALDAV_FRIENDICA_MINE:
				$sql_where = " AND cid = 0";
				break;
			case CALDAV_FRIENDICA_CONTACTS:
				$sql_where = " AND cid > 0";
				break;
			default:
				throw new Sabre_DAV_Exception_NotFound();
		}

		if ($date_from != "") {
			if (is_numeric($date_from)) $sql_where .= " AND `finish` >= '" . date("Y-m-d H:i:s", $date_from) . "'";
			else $sql_where .= " AND `finish` >= '" . dbesc($date_from) . "'";
		}
		if ($date_to != "") {
			if (is_numeric($date_to)) $sql_where .= " AND `start` <= '" . date("Y-m-d H:i:s", $date_to) . "'";
			else $sql_where .= " AND `start` <= '" . dbesc($date_to) . "'";
		}

		$ret  = array();
		$a    = get_app();
		$host = $a->get_hostname();


		$r    = q("SELECT * FROM `event` WHERE `uid` = %d " . $sql_where . " ORDER BY `start`", $uid);
		foreach ($r as $row) $ret[] =self::row2array($row, $a->timezone, $host, $uid, $namespace_id);

		return $ret;
	}


	/**
	 * @static
	 * @param int $uid
	 * @param string $uri
	 * @throws Sabre_DAV_Exception_NotFound
	 * @return array
	 */
	static public function getItemsByUri($uid = 0, $uri)
	{
		$x = explode("-", $uri);
		if ($x[0] != "friendica") throw new Sabre_DAV_Exception_NotFound();

		$namespace_id = IntVal($x[1]);
		switch ($namespace_id) {
			case CALDAV_FRIENDICA_MINE:
				$sql_where = " AND cid = 0";
				break;
			case CALDAV_FRIENDICA_CONTACTS:
				$sql_where = " AND cid > 0";
				break;
			default:
				throw new Sabre_DAV_Exception_NotFound();
		}

		$a    = get_app();
		$host = $a->get_hostname();

		$r    = q("SELECT * FROM `event` WHERE `uid` = %d AND id = %d " . $sql_where, $uid, IntVal($x[2]));
		if (count($r) != 1) throw new Sabre_DAV_Exception_NotFound();
		$ret =self::row2array($r[0], $a->timezone, $host, $uid, $namespace_id);

		return $ret;
	}


}