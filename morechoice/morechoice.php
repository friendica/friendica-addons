<?php
/**
 * Name: More Choice
 * Description: Additional gender/sexual preference/marital status options
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *    - who takes no responsibility for any additional content which may appear herein
 *
 */

use Friendica\Core\Addon;
use Friendica\Core\Config;

function morechoice_install() {

	Addon::registerHook('gender_selector', 'addon/morechoice/morechoice.php', 'morechoice_gender_selector');
	Addon::registerHook('sexpref_selector', 'addon/morechoice/morechoice.php', 'morechoice_sexpref_selector');
	Addon::registerHook('marital_selector', 'addon/morechoice/morechoice.php', 'morechoice_marital_selector');
}


function morechoice_uninstall() {

	Addon::unregisterHook('gender_selector', 'addon/morechoice/morechoice.php', 'morechoice_gender_selector');
	Addon::unregisterHook('sexpref_selector', 'addon/morechoice/morechoice.php', 'morechoice_sexpref_selector');
	Addon::unregisterHook('marital_selector', 'addon/morechoice/morechoice.php', 'morechoice_marital_selector');

// We need to leave this here for a while, because we now have a situation where people can end up with an orphaned hook.
	Addon::unregisterHook('poke_verbs', 'addon/morechoice/morechoice.php', 'morechoice_poke_verbs');

}

function morechoice_gender_selector($a,&$b) {
	$b[] = L10n::t('Androgyne');
	$b[] = L10n::t('Bear');
	$b[] = L10n::t('Bigender');
	$b[] = L10n::t('Cross dresser');
	$b[] = L10n::t('Drag queen');
	$b[] = L10n::t('Eunuch');
	$b[] = L10n::t('Faux queen');
	$b[] = L10n::t('Gender fluid');
	$b[] = L10n::t('Kathoey');
	$b[] = L10n::t('Lady');
	$b[] = L10n::t('Lipstick lesbian');
	$b[] = L10n::t('Metrosexual');
	$b[] = L10n::t('Monk');
	$b[] = L10n::t('Nun');
	$b[] = L10n::t('Soft butch');
	$b[] = L10n::t('Stone femme');
	$b[] = L10n::t('Tomboy');
	$b[] = L10n::t('Transman');
	$b[] = L10n::t('Transwoman');
	$b[] = L10n::t('Transvesti');
	$b[] = L10n::t('Trigender');
	$b[] = L10n::t('Can\'t remember');
	$b[] = L10n::t('Hard to tell these days');
}

function morechoice_sexpref_selector($a,&$b) {
	$b[] = L10n::t('Girls with big tits');
	$b[] = L10n::t('Millionaires');
	$b[] = L10n::t('Guys with big schlongs');
	$b[] = L10n::t('Easy women');
	$b[] = L10n::t('People with impaired mobility');
	$b[] = L10n::t('Amputees');
	$b[] = L10n::t('Statues, mannequins and immobility');
	$b[] = L10n::t('Pain');
	$b[] = L10n::t('Trans men');
	$b[] = L10n::t('Older women');
	$b[] = L10n::t('Asphyxiation');
	$b[] = L10n::t('In public');
	$b[] = L10n::t('In danger');
	$b[] = L10n::t('Pretending to be male');
	$b[] = L10n::t('Pretending to be female');
	$b[] = L10n::t('Breats');
	$b[] = L10n::t('Scat');
	$b[] = L10n::t('Crying');
	$b[] = L10n::t('Nappies/Diapers');
	$b[] = L10n::t('Trees');
	$b[] = L10n::t('Vomit');
	$b[] = L10n::t('Murder');
	$b[] = L10n::t('Fat people');
	$b[] = L10n::t('Feet');
	$b[] = L10n::t('Covered in insects');
	$b[] = L10n::t('Turning a human being into furniture');
	$b[] = L10n::t('Elderly people');
	$b[] = L10n::t('Transgender people');
	$b[] = L10n::t('Criminals');
	$b[] = L10n::t('Stealing');
	$b[] = L10n::t('Breast milk');
	$b[] = L10n::t('Immersing genitals in liquids');
	$b[] = L10n::t('Giants');
	$b[] = L10n::t('Masochism');
	$b[] = L10n::t('Cars');
	$b[] = L10n::t('Menstruation');
	$b[] = L10n::t('Mucus');
	$b[] = L10n::t('Obscene language');
	$b[] = L10n::t('Noses');
	$b[] = L10n::t('Navels');
	$b[] = L10n::t('Corpses');
	$b[] = L10n::t('Smells');
	$b[] = L10n::t('Buttocks');
	$b[] = L10n::t('Nonliving objects');
	$b[] = L10n::t('Sleeping people');
	$b[] = L10n::t('Urination');
	$b[] = L10n::t('Eating people');
	$b[] = L10n::t('Being eaten');
	$b[] = L10n::t('Animals');
	$b[] = L10n::t('I\'d rather just have some chocolate');
}

function morechoice_marital_selector($a,&$b) {
	$b[] = L10n::t('Married to my job');
	$b[] = L10n::t('Polygamist');
	$b[] = L10n::t('Half married');
	$b[] = L10n::t('Living in the past');
	$b[] = L10n::t('Pretending to be over my ex');
	$b[] = L10n::t('Hurt in the past');
	$b[] = L10n::t('Wallowing in self-pity');
}