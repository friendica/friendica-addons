<?php

/**
 * Name: Statistics
 * Description: Generates some statistics for http://http://the-federation.info/
 * Version: 0.1
 * Author: Michael Vogel <https://pirati.ca/profile/heluecht>
 */

function statistics_json_install() {
	register_hook('cron', 'addon/statistics_json/statistics_json.php', 'statistics_json_cron');
}


function statistics_json_uninstall() {
	unregister_hook('cron', 'addon/statistics_json/statistics_json.php', 'statistics_json_cron');
}

function statistics_json_module() {}

function statistics_json_plugin_enabled($plugin) {
	$r = q("SELECT * FROM `addon` WHERE `installed` = 1 AND `name` = '%s'", $plugin);
	return((bool)(count($r) > 0));
}

function statistics_json_init() {
	global $a;

	$statistics = array(
			"name" => $a->config["sitename"],
			"network" => FRIENDICA_PLATFORM,
			"version" => FRIENDICA_VERSION,
			"registrations_open" => ($a->config['register_policy'] != 0),
			"total_users" => get_config('statistics_json','total_users'),
			"active_users_halfyear" => get_config('statistics_json','active_users_halfyear'),
			"active_users_monthly" => get_config('statistics_json','active_users_monthly'),
			"local_posts" => get_config('statistics_json','local_posts')
			);

	$statistics["services"] = array();
	$statistics["services"]["appnet"] = statistics_json_plugin_enabled("appnet");
	$statistics["services"]["blogger"] = statistics_json_plugin_enabled("blogger");
	$statistics["services"]["buffer"] = statistics_json_plugin_enabled("buffer");
	$statistics["services"]["dreamwidth"] = statistics_json_plugin_enabled("dwpost");
	$statistics["services"]["facebook"] = statistics_json_plugin_enabled("fbpost");
	$statistics["services"]["gnusocial"] = statistics_json_plugin_enabled("statusnet");
	$statistics["services"]["googleplus"] = statistics_json_plugin_enabled("gpluspost");
	$statistics["services"]["libertree"] = statistics_json_plugin_enabled("libertree");
	$statistics["services"]["livejournal"] = statistics_json_plugin_enabled("ljpost");
	$statistics["services"]["pumpio"] = statistics_json_plugin_enabled("pumpio");
	$statistics["services"]["twitter"] = statistics_json_plugin_enabled("twitter");
	$statistics["services"]["tumblr"] = statistics_json_plugin_enabled("tumblr");
	$statistics["services"]["wordpress"] = statistics_json_plugin_enabled("wppost");

	$statistics["appnet"] = $statistics["services"]["appnet"];
	$statistics["blogger"] = $statistics["services"]["blogger"];
	$statistics["buffer"] = $statistics["services"]["buffer"];
	$statistics["dreamwidth"] = $statistics["services"]["dreamwidth"];
	$statistics["facebook"] = $statistics["services"]["facebook"];
	$statistics["gnusocial"] = $statistics["services"]["gnusocial"];
	$statistics["googleplus"] = $statistics["services"]["googleplus"];
	$statistics["libertree"] = $statistics["services"]["libertree"];
	$statistics["livejournal"] = $statistics["services"]["livejournal"];
	$statistics["pumpio"] = $statistics["services"]["pumpio"];
	$statistics["twitter"] = $statistics["services"]["twitter"];
	$statistics["tumblr"] = $statistics["services"]["tumblr"];
	$statistics["wordpress"] = $statistics["services"]["wordpress"];

	header("Content-Type: application/json");
	echo json_encode($statistics);
	logger("statistics_init: printed ".print_r($statistics, true));
	killme();
}

function statistics_json_cron($a,$b) {
	$last = get_config('statistics_json','last_calucation');

	if($last) {
		// Calculate every 24 hours
		$next = $last + (24 * 60 * 60);
		if($next > time()) {
			logger('statistics_json_cron: calculation intervall not reached');
			return;
		}
	}
        logger('statistics_json_cron: cron_start');


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

	if (is_array($users)) {
			$total_users = count($users);
			$active_users_halfyear = 0;
			$active_users_monthly = 0;

			$halfyear = time() - (180 * 24 * 60 * 60);
			$month = time() - (30 * 24 * 60 * 60);

			foreach ($users AS $user) {
				if ((strtotime($user['login_date']) > $halfyear) OR
					(strtotime($user['lastitem_date']) > $halfyear))
					++$active_users_halfyear;

				if ((strtotime($user['login_date']) > $month) OR
					(strtotime($user['lastitem_date']) > $month))
					++$active_users_monthly;

			}
			set_config('statistics_json','total_users', $total_users);
		        logger('statistics_json_cron: total_users: '.$total_users, LOGGER_DEBUG);

			set_config('statistics_json','active_users_halfyear', $active_users_halfyear);
			set_config('statistics_json','active_users_monthly', $active_users_monthly);
	}

	$posts = q("SELECT COUNT(*) AS local_posts FROM `item` WHERE `wall` AND left(body, 6) != '[share'");

	if (!is_array($posts))
		$local_posts = -1;
	else
		$local_posts = $posts[0]["local_posts"];

	set_config('statistics_json','local_posts', $local_posts);

        logger('statistics_json_cron: local_posts: '.$local_posts, LOGGER_DEBUG);

	// Now trying to register
	$url = "http://the-federation.info/register/".$a->get_hostname();
        logger('statistics_json_cron: registering url: '.$url, LOGGER_DEBUG);
	$ret = fetch_url($url);
        logger('statistics_json_cron: registering answer: '.$ret, LOGGER_DEBUG);

        logger('statistics_json_cron: cron_end');
	set_config('statistics_json','last_calucation', time());
}
