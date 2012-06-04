<?php

class FriendicaCalSourceEvents extends AnimexxCalSource
{

	/**
	 * @return int
	 */
	public static function getNamespace()
	{
		return CALDAV_NAMESPACE_FRIENDICA_NATIVE;
	}

	/**
	 * @param int $user
	 * @return array
	 */
	public function getPermissionsCalendar($user)
	{
		if ($user == $this->calendarDb->uid) return array("read"=> true, "write"=> false);
		return array("read"=> false, "write"=> false);
	}

	/**
	 * @param int $user
	 * @param string $item_uri
	 * @param string $recurrence_uri
	 * @param null|array $item_arr
	 * @return array
	 */
	public function getPermissionsItem($user, $item_uri, $recurrence_uri, $item_arr = null)
	{
		$cal_perm = $this->getPermissionsCalendar($user);
		if (!$cal_perm["read"]) return array("read"=> false, "write"=> false);
		return array("read"=> true, "write"=> false);
	}


	/**
	 * @param string $uri
	 * @param array $start
	 * @param array $end
	 * @param string $subject
	 * @param bool $allday
	 * @param string $description
	 * @param string $location
	 * @param null $color
	 * @param string $timezone
	 * @param bool $notification
	 * @param null $notification_type
	 * @param null $notification_value
	 * @throws Sabre_DAV_Exception_MethodNotAllowed
	 */
	public function updateItem($uri, $start, $end, $subject = "", $allday = false, $description = "", $location = "", $color = null, $timezone = "", $notification = true, $notification_type = null, $notification_value = null)
	{
		throw new Sabre_DAV_Exception_MethodNotAllowed();
	}

	/**
	 * @param array $start
	 * @param array $end
	 * @param string $subject
	 * @param bool $allday
	 * @param string $description
	 * @param string $location
	 * @param null $color
	 * @param string $timezone
	 * @param bool $notification
	 * @param null $notification_type
	 * @param null $notification_value
	 * @throws Sabre_DAV_Exception_MethodNotAllowed
	 * @return array|string
	 */
	public function addItem($start, $end, $subject, $allday = false, $description = "", $location = "", $color = null,
							$timezone = "", $notification = true, $notification_type = null, $notification_value = null)
	{
		throw new Sabre_DAV_Exception_MethodNotAllowed();
	}

	/**
	 * @param array $row
	 * @return array
	 */
	private function virtualData2wdcal($row) {
		$end = wdcal_mySql2PhpTime($row["data_end"]);
		if ($row["data_allday"]) $end--;
		$start = wdcal_mySql2PhpTime($row["data_start"]);
		$a = get_app();
		$arr             = array(
			"uri"               => $row["data_uri"],
			"subject"           => escape_tags($row["data_subject"]),
			"start"             => $start,
			"end"               => $end,
			"is_allday"         => ($row["data_allday"] == 1),
			"is_moredays"       => (date("Ymd", $start) != date("Ymd", $end)),
			"is_recurring"      => ($row["data_type"] == "birthday"),
			"color"             => "#ff0000",
			"is_editable"       => false,
			"is_editable_quick" => false,
			"location"          => $row["data_location"],
			"attendees"         => '',
			"has_notification"  => false,
			"url_detail"        => $a->get_baseurl() . "/dav/wdcal/" . $row["data_uri"] . "/",
			"url_edit"          => "",
			"special_type"      => ($row["data_type"] == "birthday" ? "birthday" : ""),
		);
		return $arr;
	}

	/**
	 * @param string $sd
	 * @param string $ed
	 * @param string $base_path
	 * @return array
	 */
	public function listItemsByRange($sd, $ed, $base_path)
	{
		$usr_id = IntVal($this->calendarDb->uid);

		$evs = 	FriendicaVirtualCalSourceBackend::getItemsByTime($usr_id, $this->namespace_id, $sd, $ed);
		$events = array();
		foreach ($evs as $row) $events[] = $this->virtualData2wdcal($row);

		return $events;
	}

	/**
	 * @param string $uri
	 * @throws Sabre_DAV_Exception_MethodNotAllowed
	 * @return void
	 */
	public function removeItem($uri) {
		throw new Sabre_DAV_Exception_MethodNotAllowed();
	}

	/**
	 * @param string $uri
	 * @return array
	 */
	public function getItemByUri($uri)
	{
		$usr_id = IntVal($this->calendarDb->uid);
		$row = FriendicaVirtualCalSourceBackend::getItemsByUri($usr_id, $uri);
		return $this->virtualData2wdcal($row);
	}

	/**
	 * @param string $uri
	 * @return string
	 */
	public function getItemDetailRedirect($uri) {
		$x = explode("@", $uri);
		$y = explode("-", $x[0]);
		$a = get_app();
		if (count($y) != 3) {
			goaway($a->get_baseurl() . "/dav/wdcal/");
			killme();
		}
		$a = get_app();
		$item = q("SELECT `id` FROM `item` WHERE `event-id` = %d AND `uid` = %d AND deleted = 0", IntVal($y[2]), $a->user["uid"]);
		if (count($item) == 0) return "/events/";
		return "/display/" . $a->user["nickname"] . "/" . IntVal($item[0]["id"]);
	}
}
