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
use Friendica\Core\L10n;

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
	$b['Androgyne'] = L10n::t('Androgyne');
	$b['Bear'] = L10n::t('Bear');
	$b['Bigender'] = L10n::t('Bigender');
	$b['Cross dresser'] = L10n::t('Cross dresser');
	$b['Drag queen'] = L10n::t('Drag queen');
	$b['Eunuch'] = L10n::t('Eunuch');
	$b['Faux queen'] = L10n::t('Faux queen');
	$b['Gender fluid'] = L10n::t('Gender fluid');
	$b['Kathoey'] = L10n::t('Kathoey');
	$b['Lady'] = L10n::t('Lady');
	$b['Lipstick lesbian'] = L10n::t('Lipstick lesbian');
	$b['Metrosexual'] = L10n::t('Metrosexual');
	$b['Monk'] = L10n::t('Monk');
	$b['Nun'] = L10n::t('Nun');
	$b['Soft butch'] = L10n::t('Soft butch');
	$b['Stone femme'] = L10n::t('Stone femme');
	$b['Tomboy'] = L10n::t('Tomboy');
	$b['Transman'] = L10n::t('Transman');
	$b['Transwoman'] = L10n::t('Transwoman');
	$b['Transvesti'] = L10n::t('Transvesti');
	$b['Trigender'] = L10n::t('Trigender');
	$b['Can\'t remember'] = L10n::t('Can\'t remember');
	$b['Hard to tell these days'] = L10n::t('Hard to tell these days');
}

function morechoice_sexpref_selector($a,&$b) {
	$b['Girls with big tits'] = L10n::t('Girls with big tits');
	$b['Millionaires'] = L10n::t('Millionaires');
	$b['Guys with big schlongs'] = L10n::t('Guys with big schlongs');
	$b['Easy women'] = L10n::t('Easy women');
	$b['People with impaired mobility'] = L10n::t('People with impaired mobility');
	$b['Amputees'] = L10n::t('Amputees');
	$b['Statues, mannequins and immobility'] = L10n::t('Statues, mannequins and immobility');
	$b['Pain'] = L10n::t('Pain');
	$b['Trans men'] = L10n::t('Trans men');
	$b['Older women'] = L10n::t('Older women');
	$b['Asphyxiation'] = L10n::t('Asphyxiation');
	$b['In public'] = L10n::t('In public');
	$b['In danger'] = L10n::t('In danger');
	$b['Pretending to be male'] = L10n::t('Pretending to be male');
	$b['Pretending to be female'] = L10n::t('Pretending to be female');
	$b['Breats'] = L10n::t('Breats');
	$b['Scat'] = L10n::t('Scat');
	$b['Crying'] = L10n::t('Crying');
	$b['Nappies/Diapers'] = L10n::t('Nappies/Diapers');
	$b['Trees'] = L10n::t('Trees');
	$b['Vomit'] = L10n::t('Vomit');
	$b['Murder'] = L10n::t('Murder');
	$b['Fat people'] = L10n::t('Fat people');
	$b['Feet'] = L10n::t('Feet');
	$b['Covered in insects'] = L10n::t('Covered in insects');
	$b['Turning a human being into furniture'] = L10n::t('Turning a human being into furniture');
	$b['Elderly people'] = L10n::t('Elderly people');
	$b['Transgender people'] = L10n::t('Transgender people');
	$b['Criminals'] = L10n::t('Criminals');
	$b['Stealing'] = L10n::t('Stealing');
	$b['Breast milk'] = L10n::t('Breast milk');
	$b['Immersing genitals in liquids'] = L10n::t('Immersing genitals in liquids');
	$b['Giants'] = L10n::t('Giants');
	$b['Masochism'] = L10n::t('Masochism');
	$b['Cars'] = L10n::t('Cars');
	$b['Menstruation'] = L10n::t('Menstruation');
	$b['Mucus'] = L10n::t('Mucus');
	$b['Obscene language'] = L10n::t('Obscene language');
	$b['Noses'] = L10n::t('Noses');
	$b['Navels'] = L10n::t('Navels');
	$b['Corpses'] = L10n::t('Corpses');
	$b['Smells'] = L10n::t('Smells');
	$b['Buttocks'] = L10n::t('Buttocks');
	$b['Nonliving objects'] = L10n::t('Nonliving objects');
	$b['Sleeping people'] = L10n::t('Sleeping people');
	$b['Urination'] = L10n::t('Urination');
	$b['Eating people'] = L10n::t('Eating people');
	$b['Being eaten'] = L10n::t('Being eaten');
	$b['Animals'] = L10n::t('Animals');
	$b['I\'d rather just have some chocolate'] = L10n::t('I\'d rather just have some chocolate');
}

function morechoice_marital_selector($a,&$b) {
	$b['Married to my job'] = L10n::t('Married to my job');
	$b['Polygamist'] = L10n::t('Polygamist');
	$b['Half married'] = L10n::t('Half married');
	$b['Living in the past'] = L10n::t('Living in the past');
	$b['Pretending to be over my ex'] = L10n::t('Pretending to be over my ex');
	$b['Hurt in the past'] = L10n::t('Hurt in the past');
	$b['Wallowing in self-pity'] = L10n::t('Wallowing in self-pity');
}
