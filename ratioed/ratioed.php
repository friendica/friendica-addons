<?php
/**
 * Name: Ratioed
 * Description: Additional moderation user table with statistics about user behaviour
 * Version: 0.1
 * Author: Matthew Exon <http://mat.exon.name>
 */

use Friendica\Addon\ratioed\RatioedPanel;
use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\DI;

/**
 * Sets up the addon hooks and updates data in the database if needed
 */
function ratioed_install()
{
	Hook::register('moderation_users_tabs', 'addon/ratioed/ratioed.php', 'ratioed_users_tabs');

	Logger::info("ratioed: installed");
}

/**
 * This is a statement rather than an actual function definition. The simple
 * existence of this method is checked to figure out if the addon offers a
 * module.
 */
function ratioed_module() {}

/**
 * @brief Adds additional users tab to the moderation panel
 *
 * @param array $arr Parameters, including "tabs" which is the list to modify, and "selectedTab", which is the currently selected tab ID
 */
function ratioed_users_tabs(array &$arr) {
	Logger::debug("ratioed: users tabs");

	array_push($arr['tabs'], [
		'label'	 => DI::l10n()->t('Behaviour'),
		'url'	   => 'ratioed',
		'sel'	   => $arr['selectedTab'] == 'ratioed' ? 'active' : '',
		'title'	 => DI::l10n()->t('Statistics about users behaviour'),
		'id'		=> 'admin-users-ratioed',
		'accesskey' => 'r',
	]);
}

/**
 * @brief Displays the ratioed tab in the moderation panel
 */
function ratioed_content() {
	Logger::debug("ratioed: content");

	$ratioed = DI::getDice()->create(RatioedPanel::class, [$_SERVER]);
	$httpException = DI::getDice()->create(Friendica\Module\Special\HTTPException::class);
	$ratioed->run($httpException);
}
