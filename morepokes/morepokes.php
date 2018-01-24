<?php
/**
 * Name: More Pokes
 * Description: Additional poke options
 * Version: 1.0
 * Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
 *
 */
use Friendica\Core\Addon;
use Friendica\Core\L10n;

function morepokes_install()
{
	  Addon::registerHook('poke_verbs', 'addon/morepokes/morepokes.php', 'morepokes_poke_verbs');
}

function morepokes_uninstall()
{
	  Addon::unregisterHook('poke_verbs', 'addon/morepokes/morepokes.php', 'morepokes_poke_verbs');
}

function morepokes_poke_verbs($a, &$b)
{
	$b['bitchslap'] = ['bitchslapped', L10n::t('bitchslap'), L10n::t('bitchslapped')];
	$b['shag'] = ['shag', L10n::t('shag'), L10n::t('shagged')];
	$b['somethingobscenelybiological'] = ['something obscenely biological', L10n::t('do something obscenely biological to'), L10n::t('did something obscenely biological to')];
	$b['newpokefeature'] = ['pointed out the poke feature to', L10n::t('point out the poke feature to'), L10n::t('pointed out the poke feature to')];
	$b['declareundyinglove'] = ['declared undying love for', L10n::t('declare undying love for'), L10n::t('declared undying love for')];
	$b['patent'] = ['patented', L10n::t('patent'), L10n::t('patented')];
	$b['strokebeard'] = ['stroked their beard at', L10n::t('stroke beard'), L10n::t('stroked their beard at')];
	$b['bemoan'] = ['bemoaned the declining standards of modern secondary and tertiary education to', L10n::t('bemoan the declining standards of modern secondary and tertiary education to'), L10n::t('bemoans the declining standards of modern secondary and tertiary education to')];
	$b['hugs'] = ['hugged', L10n::t('hug'), L10n::t('hugged')];
	$b['kiss'] = ['kissed', L10n::t('kiss'), L10n::t('kissed')];
	$b['raiseeyebrows'] = ['raised their eyebrows at', L10n::t('raise eyebrows at'), L10n::t('raised their eyebrows at')];
	$b['insult'] = ['insulted', L10n::t('insult'), L10n::t('insulted')];
	$b['praise'] = ['praised', L10n::t('praise'), L10n::t('praised')];
	$b['bedubiousof'] = ['was dubious of', L10n::t('be dubious of'), L10n::t('was dubious of')];
	$b['eat'] = ['ate', L10n::t('eat'), L10n::t('ate')];
	$b['giggleandfawn'] = ['giggled and fawned at', L10n::t('giggle and fawn at'), L10n::t('giggled and fawned at')];
	$b['doubt'] = ['doubted', L10n::t('doubt'), L10n::t('doubted')];
	$b['glare'] = ['glared at', L10n::t('glare'), L10n::t('glared at')];
}
