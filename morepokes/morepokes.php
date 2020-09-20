<?php
/**
 * Name: More Pokes
 * Description: Additional poke options
 * Version: 1.0
 * Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
 *
 */
use Friendica\Core\Hook;
use Friendica\DI;

function morepokes_install()
{
	  Hook::register('poke_verbs', 'addon/morepokes/morepokes.php', 'morepokes_poke_verbs');
}

function morepokes_poke_verbs($a, &$b)
{
	$b['bitchslap'] = ['bitchslapped', DI::l10n()->t('bitchslap'), DI::l10n()->t('bitchslapped')];
	$b['shag'] = ['shag', DI::l10n()->t('shag'), DI::l10n()->t('shagged')];
	$b['somethingobscenelybiological'] = ['something obscenely biological', DI::l10n()->t('do something obscenely biological to'), DI::l10n()->t('did something obscenely biological to')];
	$b['newpokefeature'] = ['pointed out the poke feature to', DI::l10n()->t('point out the poke feature to'), DI::l10n()->t('pointed out the poke feature to')];
	$b['declareundyinglove'] = ['declared undying love for', DI::l10n()->t('declare undying love for'), DI::l10n()->t('declared undying love for')];
	$b['patent'] = ['patented', DI::l10n()->t('patent'), DI::l10n()->t('patented')];
	$b['strokebeard'] = ['stroked their beard at', DI::l10n()->t('stroke beard'), DI::l10n()->t('stroked their beard at')];
	$b['bemoan'] = ['bemoaned the declining standards of modern secondary and tertiary education to', DI::l10n()->t('bemoan the declining standards of modern secondary and tertiary education to'), DI::l10n()->t('bemoans the declining standards of modern secondary and tertiary education to')];
	$b['hugs'] = ['hugged', DI::l10n()->t('hug'), DI::l10n()->t('hugged')];
	$b['kiss'] = ['kissed', DI::l10n()->t('kiss'), DI::l10n()->t('kissed')];
	$b['raiseeyebrows'] = ['raised their eyebrows at', DI::l10n()->t('raise eyebrows at'), DI::l10n()->t('raised their eyebrows at')];
	$b['insult'] = ['insulted', DI::l10n()->t('insult'), DI::l10n()->t('insulted')];
	$b['praise'] = ['praised', DI::l10n()->t('praise'), DI::l10n()->t('praised')];
	$b['bedubiousof'] = ['was dubious of', DI::l10n()->t('be dubious of'), DI::l10n()->t('was dubious of')];
	$b['eat'] = ['ate', DI::l10n()->t('eat'), DI::l10n()->t('ate')];
	$b['giggleandfawn'] = ['giggled and fawned at', DI::l10n()->t('giggle and fawn at'), DI::l10n()->t('giggled and fawned at')];
	$b['doubt'] = ['doubted', DI::l10n()->t('doubt'), DI::l10n()->t('doubted')];
	$b['glare'] = ['glared at', DI::l10n()->t('glare'), DI::l10n()->t('glared at')];
}
