<?php
/**
 * Name: More Pokes
 * Description: Additional poke options
 * Version: 1.0
 * Author: Thomas Willingham <https://kakste.com/profile/beardyunixer>.
 */
function morepokes_install()
{
    register_hook('poke_verbs', 'addon/morepokes/morepokes.php', 'morepokes_poke_verbs');
}

function morepokes_uninstall()
{
    unregister_hook('poke_verbs', 'addon/morepokes/morepokes.php', 'morepokes_poke_verbs');
}

function morepokes_poke_verbs($a, &$b)
{
    $b['bitchslap'] = array('bitchslapped', t('bitchslap'), t('bitchslapped'));
    $b['shag'] = array('shag', t('shag'), t('shagged'));
    $b['somethingobscenelybiological'] = array('something obscenely biological', t('do something obscenely biological to'), t('did something obscenely biological to'));
    $b['newpokefeature'] = array('pointed out the poke feature to', t('point out the poke feature to'), t('pointed out the poke feature to'));
    $b['declareundyinglove'] = array('declared undying love for', t('declare undying love for'), t('declared undying love for'));
    $b['patent'] = array('patented', t('patent'), t('patented'));
    $b['strokebeard'] = array('stroked their beard at', t('stroke beard'), t('stroked their beard at'));
    $b['bemoan'] = array('bemoaned the declining standards of modern secondary and tertiary education to', t('bemoan the declining standards of modern secondary and tertiary education to'), t('bemoans the declining standards of modern secondary and tertiary education to'));
    $b['hugs'] = array('hugged', t('hug'), t('hugged'));
    $b['kiss'] = array('kissed', t('kiss'), t('kissed'));
    $b['raiseeyebrows'] = array('raised their eyebrows at', t('raise eyebrows at'), t('raised their eyebrows at'));
    $b['insult'] = array('insulted', t('insult'), t('insulted'));
    $b['praise'] = array('praised', t('praise'), t('praised'));
    $b['bedubiousof'] = array('was dubious of', t('be dubious of'), t('was dubious of'));
    $b['eat'] = array('ate', t('eat'), t('ate'));
    $b['giggleandfawn'] = array('giggled and fawned at', t('giggle and fawn at'), t('giggled and fawned at'));
    $b['doubt'] = array('doubted', t('doubt'), t('doubted'));
    $b['glare'] = array('glared at', t('glare'), t('glared at'));
}
