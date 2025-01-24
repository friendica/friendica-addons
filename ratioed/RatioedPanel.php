<?php

namespace Friendica\Addon\ratioed;

use Friendica\Content\Pager;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;
use Friendica\Model\Verb;
use Friendica\Module\Moderation\Users\Active;
use Friendica\Protocol\Activity;

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

		$uid	= $this->parameters['uid']	?? 0;
		$user   = [];

		if ($uid) {
			$user = User::getById($uid, ['username', 'blocked']);
			if (!$user) {
				$this->systemMessages->addNotice($this->t('User not found'));
				$this->baseUrl->redirect('ratioed');
			}
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
		$order_direction = '+';
		if (!empty($_REQUEST['o'])) {
			$new_order = $_REQUEST['o'];
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
			$this->t('Replies last month'),
			$this->t('Reply likes'),
			$this->t('Respondee likes'),
			$this->t('OP likes'),
			$this->t('Reply guy score'),
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
			'reply_count',
			'reply_likes',
			'reply_respondee_likes',
			'reply_op_likes',
			'reply_guy_score',
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

	protected function getReplyGuyRow($contact_uid)
	{
		$like_vid = Verb::getID(Activity::LIKE);
		$post_vid = Verb::getID(Activity::POST);

		/*
		 * This is a complicated query.
		 *
		 * The innermost select retrieves a chain of four posts: an
		 * original post, a target comment (possibly deep down in the
		 * thread), a reply from our user, and a like for that reply.
		 * If there's no like, we still want to count the reply, so we
		 * use an outer join.
		 *
		 * The second select adds "points" for different kinds of
		 * likes.  The outermost select then counts up these points,
		 * and the number of distinct replies.
		 */
		$reply_guy_result = DBA::p('
SELECT
  COUNT(distinct reply_id) AS replies_total,
  SUM(like_point) AS like_total,
  SUM(target_like_point) AS target_like_total,
  SUM(original_like_point) AS original_like_total
FROM (
  SELECT
	reply_id,
	like_date,
	like_date IS NOT NULL AS like_point,
	like_author = target_author AS target_like_point,
	like_author = original_author AS original_like_point
  FROM (
	SELECT
	  original_post.`uri-id` AS original_id,
	  original_post.`author-id` AS original_author,
	  original_post.created AS original_date,
	  target_post.`uri-id` AS target_id,
	  target_post.`author-id` AS target_author,
	  target_post.created AS target_date,
	  reply_post.`uri-id` AS reply_id,
	  reply_post.`author-id` AS reply_author,
	  reply_post.created AS reply_date,
	  like_post.`uri-id` AS like_id,
	  like_post.`author-id` AS like_author,
	  like_post.created AS like_date
	FROM
	  post AS original_post
	JOIN
	  post AS target_post
	ON
	  original_post.`uri-id` = target_post.`parent-uri-id`
	JOIN
	  post AS reply_post
	ON
	  target_post.`uri-id` = reply_post.`thr-parent-id` AND
	  reply_post.`author-id` = ? AND
	  reply_post.`author-id` != target_post.`author-id` AND
	  reply_post.`author-id` != original_post.`author-id` AND
	  reply_post.`uri-id` != reply_post.`thr-parent-id` AND
	  reply_post.vid = ? AND
	  reply_post.created > CURDATE() - INTERVAL 1 MONTH
	LEFT OUTER JOIN
	  post AS like_post
	ON
	  reply_post.`uri-id` = like_post.`thr-parent-id` AND
	  like_post.vid = ? AND
	  like_post.`author-id` != reply_post.`author-id`
  ) AS post_meta
) AS reply_counts
', $contact_uid, $post_vid, $like_vid);
		return $reply_guy_result;
	}

	// https://stackoverflow.com/a/48283297/235936
	protected function sigFig($value, $digits)
	{
		if ($value == 0) {
			$decimalPlaces = $digits - 1;
		} elseif ($value < 0) {
			$decimalPlaces = $digits - floor(log10($value * -1)) - 1;
		} else {
			$decimalPlaces = $digits - floor(log10($value)) - 1;
		}

		$answer = ($decimalPlaces > 0) ?
			number_format($value, $decimalPlaces) : round($value, $decimalPlaces);
		return $answer;
	}

	protected function fillReplyGuyData(&$user)
	{
		$reply_guy_result = $this->getReplyGuyRow($user['user_contact_uid']);
		if (DBA::isResult($reply_guy_result)) {
			$reply_guy_result_row = DBA::fetch($reply_guy_result);
			$user['reply_count'] = (int) $reply_guy_result_row['replies_total'] ?? 0;
			$user['reply_likes'] = (int) $reply_guy_result_row['like_total'] ?? 0;
			$user['reply_respondee_likes'] = (int) $reply_guy_result_row['target_like_total'] ?? 0;
			$user['reply_op_likes'] = (int) $reply_guy_result_row['original_like_total'] ?? 0;

			$denominator = $user['reply_likes'] + $user['reply_respondee_likes'] + $user['reply_op_likes'];
			if ($user['reply_count'] === 0) {
				$user['reply_guy'] = false;
				$user['reply_guy_score'] = 0;
			} elseif ($denominator == 0) {
				$user['reply_guy'] = true;
				$user['reply_guy_score'] = '∞';
			} else {
				$reply_guy_score = $user['reply_count'] / $denominator;
				$user['reply_guy'] = $reply_guy_score >= 1.0;
				$user['reply_guy_score'] = $this->sigFig($reply_guy_score, 2);
			}
		} else {
			$user['reply_count'] = "error";
			$user['reply_likes'] = "error";
			$user['reply_respondee_likes'] = "error";
			$user['reply_op_likes'] = "error";
			$user['reply_guy'] = false;
			$user['reply_guy_score'] = 0;
		}
	}

	protected function setupUserCallback(): \Closure
	{
		DI::logger()->debug("ratioed: setupUserCallback");
		$parentCallback = parent::setupUserCallback();
		return function ($user) use ($parentCallback) {
			$blocked_count = DBA::count('user-contact', ['uid' => $user['uid'], 'is-blocked' => 1]);
			$user['blocked_by'] = $blocked_count;

			$self_contact_result = DBA::p('SELECT admin_contact.id AS user_contact_uid FROM contact AS admin_contact JOIN contact AS user_contact ON admin_contact.`uri-id` = user_contact.`uri-id` AND admin_contact.self = 0 AND user_contact.self = 1 WHERE user_contact.uid = ?', $user['uid']);
			if (DBA::isResult($self_contact_result)) {
				$self_contact_result_row = DBA::fetch($self_contact_result);
				$user['user_contact_uid'] = $self_contact_result_row['user_contact_uid'];
			} else {
				$user['user_contact_uid'] = null;
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
					} else {
						$user['reactions'] = 0;
						if ($user['comments'] == 0) {
							$user['comments'] = 0;
							$user['ratio'] = 0;
							$user['ratioed'] = false;
						} else {
							$user['ratio'] = '∞';
							$user['ratioed'] = false;
						}
					}
				} else {
					$user['comments'] = 'error';
					$user['reactions'] = 'error';
					$user['ratio'] = 'error';
					$user['ratioed'] = false;
				}
			} else {
				$user['comments'] = 'error';
				$user['reactions'] = 'error';
				$user['ratio'] = 'error';
				$user['ratioed'] = false;
			}

			$this->fillReplyGuyData($user);

			$user = $parentCallback($user);
			DI::logger()->debug("ratioed: setupUserCallback", [
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
