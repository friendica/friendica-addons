<?php

/**
 * Name: Statistics
 * Description: Generates some statistics for http://pods.jasonrobinson.me/
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function statistics_json_install() {
}


function statistics_json_uninstall() {
}

function statistics_json_module() {}

function statistics_json_init() {
	global $a;

	$statistics = array(
			"name" => $a->config["sitename"],
			"version" => FRIENDICA_VERSION,
			"registrations_open" => ($a->config['register_policy'] != 0),
			);

	$users = q("SELECT profile.*, `user`.`login_date`, `lastitem`.`lastitem_date`
			FROM (SELECT MAX(`item`.`changed`) as `lastitem_date`, `item`.`uid`
				FROM `item`
					WHERE `item`.`type` = 'wall'
						GROUP BY `item`.`uid`) AS `lastitem`
						RIGHT OUTER JOIN `user` ON `user`.`uid` = `lastitem`.`uid`, `contact`, `profile`
                                WHERE
					`user`.`uid` = `contact`.`uid` AND `profile`.`uid` = `user`.`uid`
					AND `profile`.`is-default` AND (`profile`.`publish` OR `profile`.`net-publish`)
					AND `user`.`verified` AND `contact`.`self`
					AND NOT `user`.`blocked`
					AND NOT `user`.`account_removed`
					AND NOT `user`.`account_expired`");

	if (!is_array($users)) {
			$statistics["total_users"] = -1;
			$statistics["active_users_halfyear"] = -1;
			$statistics["active_users_monthly"] = -1;
	} else {
			$statistics["total_users"] = count($users);
			$statistics["active_users_halfyear"] = 0;
			$statistics["active_users_monthly"] = 0;

			$halfyear = time() - (180 * 24 * 60 * 60);
			$month = time() - (30 * 24 * 60 * 60);

			foreach ($users AS $user) {
				if ((strtotime($user['login_date']) > $halfyear) OR
					(strtotime($user['lastitem_date']) > $halfyear))
					++$statistics["active_users_halfyear"];

				if ((strtotime($user['login_date']) > $month) OR
					(strtotime($user['lastitem_date']) > $month))
					++$statistics["active_users_monthly"];

			}
	}

	$posts = q("SELECT COUNT(*) AS local_posts FROM `item` WHERE `wall`");
	if (!is_array($posts))
		$statistics["local_posts"] = -1;
	else
		$statistics["local_posts"] = $posts[0]["local_posts"];

	header("Content-Type: application/json");
	echo json_encode($statistics);
print_r($users);
	logger("statistics_init: printed ".print_r($statistics, true));
	killme();
}
