<?php
/**
 * Name: More Pokes
 * Description: Additional poke options
 * Version: 1.0
 * Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>
 *
 */
use Friendica\Core\Addon;

function morepokes_install() {
	  Addon::registerHook('poke_verbs', 'addon/morepokes/morepokes.php', 'morepokes_poke_verbs');
}

function morepokes_uninstall() {
	  Addon::unregisterHook('poke_verbs', 'addon/morepokes/morepokes.php', 'morepokes_poke_verbs');
}

function morepokes_poke_verbs($a,&$b) {
	$b['bitchslap'] = ['bitchslapped', t('bitchslap'), t('bitchslapped')];
	$b['shag'] = ['shag', t('shag'), t('shagged')];
	$b['somethingobscenelybiological'] = ['something obscenely biological', t('do something obscenely biological to'), t('did something obscenely biological to')];
	$b['newpokefeature'] = ['pointed out the poke feature to', t('point out the poke feature to'), t('pointed out the poke feature to')];
	$b['declareundyinglove'] = ['declared undying love for', t('declare undying love for'), t('declared undying love for')];
	$b['patent'] = ['patented', t('patent'), t('patented')];
	$b['strokebeard'] = ['stroked their beard at', t('stroke beard'), t('stroked their beard at')];
	$b['bemoan'] = ['bemoaned the declining standards of modern secondary and tertiary education to', t('bemoan the declining standards of modern secondary and tertiary education to'), t('bemoans the declining standards of modern secondary and tertiary education to')];
	$b['hugs'] = ['hugged', t('hug'), t('hugged')];
	$b['kiss'] = ['kissed', t('kiss'), t('kissed')];
	$b['raiseeyebrows'] = ['raised their eyebrows at', t('raise eyebrows at'), t('raised their eyebrows at')];
	$b['insult'] = ['insulted', t('insult'), t('insulted')];
	$b['praise'] = ['praised', t('praise'), t('praised')];
	$b['bedubiousof'] = ['was dubious of', t('be dubious of'), t('was dubious of')];
	$b['eat'] = ['ate', t('eat'), t('ate')];
	$b['giggleandfawn'] = ['giggled and fawned at', t('giggle and fawn at'), t('giggled and fawned at')];
	$b['doubt'] = ['doubted', t('doubt'), t('doubted')];
	$b['glare'] = ['glared at', t('glare'), t('glared at')];
;}
