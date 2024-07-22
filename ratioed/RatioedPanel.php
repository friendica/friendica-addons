<?php

namespace Friendica\Addon\ratioed;

use Friendica\Content\Pager;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;
use Friendica\Module\Moderation\Users\Active;

/**
 * This class implements the "Behaviour" panel in Moderation/Users
 */
class RatioedPanel extends Active
{
	protected function content(array $request = []): string
	{
		Active::content();

		if (isset(DI::args()->getArgv()[1]) and DI::args()->getArgv()[1] === 'help') {
			$template = Renderer::getMarkupTemplate('/help.tpl', 'addon/ratioed/');
			return Renderer::replaceMacros($template, array('$config' => DI::baseUrl() . '/settings/addon'));
		}

		$action = $this->parameters['action'] ?? '';
		$uid	= $this->parameters['uid']	?? 0;

		if ($uid) {
			$user = User::getById($uid, ['username', 'blocked']);
			if (!$user) {
				$this->systemMessages->addNotice($this->t('User not found'));
				$this->baseUrl->redirect('moderation/users');
			}
		}

		switch ($action) {
			case 'delete':
				if ($this->session->getLocalUserId() != $uid) {
					self::checkFormSecurityTokenRedirectOnError('moderation/users/active', 'moderation_users_active', 't');
					// delete user
					User::remove($uid);

					$this->systemMessages->addNotice($this->t('User "%s" deleted', $user['username']));
				} else {
					$this->systemMessages->addNotice($this->t('You can\'t remove yourself'));
				}

				$this->baseUrl->redirect('moderation/users/active');
				break;
			case 'block':
				self::checkFormSecurityTokenRedirectOnError('moderation/users/active', 'moderation_users_active', 't');
				User::block($uid);
				$this->systemMessages->addNotice($this->t('User "%s" blocked', $user['username']));
				$this->baseUrl->redirect('moderation/users/active');
				break;
		}
		$pager = new Pager($this->l10n, $this->args->getQueryString(), 100);

		$valid_orders = [
			'name',
			'email',
			'register_date',
			'last-activity',
			'last-item',
			'page-flags',
		];

		$order		   = 'last-item';
		$order_direction = '-';
		if (!empty($request['o'])) {
			$new_order = $request['o'];
			if ($new_order[0] === '-') {
				$order_direction = '-';
				$new_order	   = substr($new_order, 1);
			}

			if (in_array($new_order, $valid_orders)) {
				$order = $new_order;
			}
		}

		$users = User::getList($pager->getStart(), $pager->getItemsPerPage(), 'active', $order, ($order_direction == '-'));

		$users = array_map($this->setupUserCallback(), $users);

		$header_titles = [
			$this->t('Name'),
			$this->t('Email'),
			$this->t('Register date'),
			$this->t('Last login'),
			$this->t('Last public item'),
			$this->t('Type'),
			$this->t('Blocked by'),
			$this->t('Comments last 24h'),
			$this->t('Reactions last 24h'),
			$this->t('Ratio last 24h'),
		];
		$field_names = [
			'name',
			'email',
			'register_date',
			'login_date',
			'lastitem_date',
			'page_flags',
			'blocked_by',
			'comments',
			'reactions',
			'ratio',
		];
		$th_users = array_map(null, $header_titles, $valid_orders, $field_names);

		$count = $this->database->count('user', ["`verified` AND NOT `blocked` AND NOT `account_removed` AND NOT `account_expired` AND `uid` != ?", 0]);

		$t = Renderer::getMarkupTemplate('ratioed.tpl', 'addon/ratioed');
		return self::getTabsHTML('ratioed') . Renderer::replaceMacros($t, [
			// strings //
			'$title'		  => $this->t('Moderation'),
			'$help_url'		  => $this->baseUrl . '/ratioed/help',
			'$page'		   => $this->t('Behaviour'),
			'$select_all'	 => $this->t('select all'),
			'$delete'		 => $this->t('Delete'),
			'$block'		  => $this->t('Block'),
			'$blocked'		=> $this->t('User blocked'),
			'$siteadmin'	  => $this->t('Site admin'),
			'$accountexpired' => $this->t('Account expired'),
			'$h_newuser'	  => $this->t('Create a new user'),

			'$th_users'			  => $th_users,
			'$order_users'		   => $order,
			'$order_direction_users' => $order_direction,

			'$confirm_delete_multi' => $this->t('Selected users will be deleted!\n\nEverything these users had posted on this site will be permanently deleted!\n\nAre you sure?'),
			'$confirm_delete'	   => $this->t('The user {0} will be deleted!\n\nEverything this user has posted on this site will be permanently deleted!\n\nAre you sure?'),

			'$form_security_token' => self::getFormSecurityToken('moderation_users_active'),

			// values //
			'$baseurl'	  => $this->baseUrl,
			'$query_string' => $this->args->getQueryString(),

			'$users' => $users,
			'$count' => $count,
			'$pager' => $pager->renderFull($count),
		]);
	}

	protected function setupUserCallback(): \Closure
	{
		Logger::debug("ratioed: setupUserCallback");
		$parentCallback = parent::setupUserCallback();
		return function ($user) use ($parentCallback) {
			$blocked_count = DBA::count('user-contact', ['uid' => $user['uid'], 'is-blocked' => 1]);
			$user['blocked_by'] = $blocked_count;

			$self_contact_result = DBA::p('SELECT admin_contact.id AS user_contact_uid FROM contact AS admin_contact JOIN contact AS user_contact ON admin_contact.`uri-id` = user_contact.`uri-id` AND admin_contact.self = 0 AND user_contact.self = 1 WHERE user_contact.uid = ?', $user['uid']);
			if (DBA::isResult($self_contact_result)) {
				$self_contact_result_row = DBA::fetch($self_contact_result);
				$user['user_contact_uid'] = $self_contact_result_row['user_contact_uid'];
			}
			else {
				$user['user_contact_uid'] = NULL;
			}

			if ($user['user_contact_uid']) {
				$post_engagement_result = DBA::p('SELECT SUM(`comments`) AS `comment_count`, SUM(`activities`) AS `activities_count` FROM `post-engagement` WHERE `post-engagement`.created > DATE_SUB(now(), INTERVAL 1 DAY) AND `post-engagement`.`owner-id` = ?', $user['user_contact_uid']);
				if (DBA::isResult($post_engagement_result)) {
					$post_engagement_result_row = DBA::fetch($post_engagement_result);
					$user['comments'] = $post_engagement_result_row['comment_count'];
					$user['reactions'] = $post_engagement_result_row['activities_count'];
					if ($user['reactions'] > 0) {
						$user['ratio'] = number_format($user['comments'] / $user['reactions'], 1, '.', '');
						$user['ratioed'] = (float)($user['ratio']) >= 2.0;
					}
					else {
						if ($user['comments'] == 0) {
							$user['ratio'] = '0';
							$user['ratioed'] = false;
						}
						else {
							$user['ratio'] = '∞';
							$user['ratioed'] = false;
						}
					}
				}
				else {
					$user['comments'] = 'error';
					$user['reactions'] = 'error';
					$user['ratio'] = 'error';
					$user['ratioed'] = false;
				}
			}
			else {
				$user['comments'] = 'error';
				$user['reactions'] = 'error';
				$user['ratio'] = 'error';
				$user['ratioed'] = false;
			}

			$user = $parentCallback($user);
			Logger::debug("ratioed: setupUserCallback", [
				'uid' => $user['uid'],
				'blocked_by' => $user['blocked_by'],
				'comments' => $user['comments'],
				'reactions' => $user['reactions'],
				'ratio' => $user['ratio'],
				'ratioed' => $user['ratioed'],
			]);
			return $user;
		};
	}
}
