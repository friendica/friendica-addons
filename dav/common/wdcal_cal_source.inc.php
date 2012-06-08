<?php


abstract class AnimexxCalSource
{

	/**
	 * @var int $namespace_id
	 */
	protected $namespace_id;

	/**
	 * @var DBClass_friendica_calendars $calendarDb
	 */
	protected $calendarDb;

	/**
	 * @var int
	 */
	protected $user_id;


	/**
	 * @param int $user_id
	 * @param int $namespace_id
	 * @throws Sabre_DAV_Exception_NotFound
	 */
	function __construct($user_id = 0, $namespace_id = 0)
	{
		$this->namespace_id = IntVal($namespace_id);
		$this->user_id = IntVal($user_id);

		$x                  = q("SELECT * FROM %s%scalendars WHERE `namespace` = %d AND `namespace_id` = %d AND `uid` = %d",
			CALDAV_SQL_DB, CALDAV_SQL_PREFIX, $this->getNamespace(), $this->namespace_id, $this->user_id
		);

		if (count($x) != 1) throw new Sabre_DAV_Exception_NotFound("Not found");

		try {
			$this->calendarDb = new DBClass_friendica_calendars($x[0]);
		} catch (Exception $e) {
			throw new Sabre_DAV_Exception_NotFound("Not found");
		}
	}

	/**
	 * @abstract
	 * @return int
	 */
	public static abstract function getNamespace();

	/**
	 * @abstract
	 * @param int $user
	 * @return array
	 */
	public abstract function getPermissionsCalendar($user);

	/**
	 * @abstract
	 * @param int $user
	 * @param string $item_uri
	 * @param string $recurrence_uri
	 * @param array|null $item_arr
	 * @return array
	 */
	public abstract function getPermissionsItem($user, $item_uri, $recurrence_uri, $item_arr = null);

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
	 */
	public abstract function updateItem($uri, $start, $end, $subject = "", $allday = false, $description = "", $location = "", $color = null,
										$timezone = "", $notification = true, $notification_type = null, $notification_value = null);


	/**
	 * @abstract
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
	 * @return array
	 */
	public abstract function addItem($start, $end, $subject, $allday = false, $description = "", $location = "", $color = null,
									 $timezone = "", $notification = true, $notification_type = null, $notification_value = null);


	/**
	 * @param string $uri
	 */
	public abstract function removeItem($uri);


	/**
	 * @abstract
	 * @param string $sd
	 * @param string $ed
	 * @param string $base_path
	 * @return array
	 */
	public abstract function listItemsByRange($sd, $ed, $base_path);


	/**
	 * @abstract
	 * @param string $uri
	 * @return array
	 */
	public abstract function getItemByUri($uri);


	/**
	 * @param string $uri
	 * @return null|string
	 */
	public function getItemDetailRedirect($uri) {
		return null;
	}

}
