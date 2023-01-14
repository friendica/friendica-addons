<?php
/**
 * Name: rendertime
 * Description: Shows the time that was needed to render the current page
 * Version: 0.1
 * Author: Michael Vogel <http://pirati.ca/profile/heluecht>
 *
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\DI;

function rendertime_install() {
	Hook::register('page_end', 'addon/rendertime/rendertime.php', 'rendertime_page_end');
	DI::config()->set('system', 'profiler', true);
}

function rendertime_uninstall()
{
	DI::config()->delete('system', 'profiler');
}

function rendertime_init_1()
{
}

function rendertime_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/rendertime/');

	$o = Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Save Settings'),
		'$callstack' => ['callstack', DI::l10n()->t('Show callstack'), DI::config()->get('rendertime', 'callstack'), DI::l10n()->t('Show detailed performance measures in the callstack. When deactivated, only the summary will be displayed.')],
		'$minimal_time' => ['minimal_time', DI::l10n()->t('Minimal time'), DI::config()->get('rendertime', 'minimal_time'), DI::l10n()->t('Minimal time that an activity needs to be listed in the callstack.')],
	]);
}

function rendertime_addon_admin_post()
{
	DI::config()->set('rendertime', 'callstack', $_POST['callstack'] ?? false);
	DI::config()->set('rendertime', 'minimal_time', $_POST['minimal_time'] ?? 0);
}

/**
 * @param string $o
 */
function rendertime_page_end(string &$o)
{
	$profiler = DI::profiler();

	$duration = microtime(true) - $profiler->get('start');

	$ignored_modules = [
		\Friendica\Module\Media\Photo\Browser::class,
		\Friendica\Module\Media\Attachment\Browser::class,
	];
	$ignored = in_array(DI::router()->getModuleClass(), $ignored_modules);

	if (DI::userSession()->isSiteAdmin() && (($_GET['mode'] ?? '') != 'minimal') && !DI::mode()->isMobile() && !DI::mode()->isMobile() && !$ignored) {

		$o = $o . '<div class="renderinfo">' . DI::l10n()->t("Database: %s/%s, Network: %s, Rendering: %s, Session: %s, I/O: %s, Other: %s, Total: %s",
				round($profiler->get('database') - $profiler->get('database_write'), 3),
				round($profiler->get('database_write'), 3),
				round($profiler->get('network'), 2),
				round($profiler->get('rendering'), 2),
				round($profiler->get('session'), 2),
				round($profiler->get('file'), 2),
				round($duration - $profiler->get('database')
					- $profiler->get('network') - $profiler->get('rendering')
					- $profiler->get('session') - $profiler->get('file'), 2),
				round($duration, 2)
			//round($profiler->get('markstart'), 3)
			//round($profiler->get('plugin'), 3)
			) . '</div>';

			$total = microtime(true) - $profiler->get('start');
			$rest = $total - ($profiler->get('ready') - $profiler->get('start')) - $profiler->get('init') - $profiler->get('content');
			$o = $o . '<div class="renderinfo">' . DI::l10n()->t("Class-Init: %s, Boot: %s, Init: %s, Content: %s, Other: %s, Total: %s",
				round($profiler->get('classinit') - $profiler->get('start'), 3),
				round($profiler->get('ready') - $profiler->get('classinit'), 3),
				round($profiler->get('init'), 3),
				round($profiler->get('content'), 3),
				round($rest, 3),
				round($total, 3)
				) . '</div>';

		if ($profiler->isRendertime()) {
			$o .= '<pre>';
			$o .= $profiler->getRendertimeString(floatval(DI::config()->get('rendertime', 'minimal_time', 0)));
			$o .= '</pre>';
		}
	}
}
