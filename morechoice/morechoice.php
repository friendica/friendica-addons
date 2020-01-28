<?php
/**
 * Name: More Choice
 * Description: Additional gender/sexual preference/marital status options
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *    - who takes no responsibility for any additional content which may appear herein
 * Status: Deprecated
 */

use Friendica\Core\Hook;
use Friendica\DI;

function morechoice_install() {

	Hook::register('gender_selector', 'addon/morechoice/morechoice.php', 'morechoice_gender_selector');
	Hook::register('sexpref_selector', 'addon/morechoice/morechoice.php', 'morechoice_sexpref_selector');
	Hook::register('marital_selector', 'addon/morechoice/morechoice.php', 'morechoice_marital_selector');
}


function morechoice_uninstall() {

	Hook::unregister('gender_selector', 'addon/morechoice/morechoice.php', 'morechoice_gender_selector');
	Hook::unregister('sexpref_selector', 'addon/morechoice/morechoice.php', 'morechoice_sexpref_selector');
	Hook::unregister('marital_selector', 'addon/morechoice/morechoice.php', 'morechoice_marital_selector');

// We need to leave this here for a while, because we now have a situation where people can end up with an orphaned hook.
	Hook::unregister('poke_verbs', 'addon/morechoice/morechoice.php', 'morechoice_poke_verbs');

}

function morechoice_gender_selector($a,&$b) {
	$b['Androgyne'] = DI::l10n()->t('Androgyne');
	$b['Bear'] = DI::l10n()->t('Bear');
	$b['Bigender'] = DI::l10n()->t('Bigender');
	$b['Cross dresser'] = DI::l10n()->t('Cross dresser');
	$b['Drag queen'] = DI::l10n()->t('Drag queen');
	$b['Eunuch'] = DI::l10n()->t('Eunuch');
	$b['Faux queen'] = DI::l10n()->t('Faux queen');
	$b['Gender fluid'] = DI::l10n()->t('Gender fluid');
	$b['Kathoey'] = DI::l10n()->t('Kathoey');
	$b['Lady'] = DI::l10n()->t('Lady');
	$b['Lipstick lesbian'] = DI::l10n()->t('Lipstick lesbian');
	$b['Metrosexual'] = DI::l10n()->t('Metrosexual');
	$b['Monk'] = DI::l10n()->t('Monk');
	$b['Nun'] = DI::l10n()->t('Nun');
	$b['Soft butch'] = DI::l10n()->t('Soft butch');
	$b['Stone femme'] = DI::l10n()->t('Stone femme');
	$b['Tomboy'] = DI::l10n()->t('Tomboy');
	$b['Transman'] = DI::l10n()->t('Transman');
	$b['Transwoman'] = DI::l10n()->t('Transwoman');
	$b['Transvesti'] = DI::l10n()->t('Transvesti');
	$b['Trigender'] = DI::l10n()->t('Trigender');
	$b['Can\'t remember'] = DI::l10n()->t('Can\'t remember');
	$b['Hard to tell these days'] = DI::l10n()->t('Hard to tell these days');
}

function morechoice_sexpref_selector($a,&$b) {
	$b['Girls with big tits'] = DI::l10n()->t('Girls with big tits');
	$b['Millionaires'] = DI::l10n()->t('Millionaires');
	$b['Guys with big schlongs'] = DI::l10n()->t('Guys with big schlongs');
	$b['Easy women'] = DI::l10n()->t('Easy women');
	$b['People with impaired mobility'] = DI::l10n()->t('People with impaired mobility');
	$b['Amputees'] = DI::l10n()->t('Amputees');
	$b['Statues, mannequins and immobility'] = DI::l10n()->t('Statues, mannequins and immobility');
	$b['Pain'] = DI::l10n()->t('Pain');
	$b['Trans men'] = DI::l10n()->t('Trans men');
	$b['Older women'] = DI::l10n()->t('Older women');
	$b['Asphyxiation'] = DI::l10n()->t('Asphyxiation');
	$b['In public'] = DI::l10n()->t('In public');
	$b['In danger'] = DI::l10n()->t('In danger');
	$b['Pretending to be male'] = DI::l10n()->t('Pretending to be male');
	$b['Pretending to be female'] = DI::l10n()->t('Pretending to be female');
	$b['Breats'] = DI::l10n()->t('Breats');
	$b['Scat'] = DI::l10n()->t('Scat');
	$b['Crying'] = DI::l10n()->t('Crying');
	$b['Nappies/Diapers'] = DI::l10n()->t('Nappies/Diapers');
	$b['Trees'] = DI::l10n()->t('Trees');
	$b['Vomit'] = DI::l10n()->t('Vomit');
	$b['Murder'] = DI::l10n()->t('Murder');
	$b['Fat people'] = DI::l10n()->t('Fat people');
	$b['Feet'] = DI::l10n()->t('Feet');
	$b['Covered in insects'] = DI::l10n()->t('Covered in insects');
	$b['Turning a human being into furniture'] = DI::l10n()->t('Turning a human being into furniture');
	$b['Elderly people'] = DI::l10n()->t('Elderly people');
	$b['Transgender people'] = DI::l10n()->t('Transgender people');
	$b['Criminals'] = DI::l10n()->t('Criminals');
	$b['Stealing'] = DI::l10n()->t('Stealing');
	$b['Breast milk'] = DI::l10n()->t('Breast milk');
	$b['Immersing genitals in liquids'] = DI::l10n()->t('Immersing genitals in liquids');
	$b['Giants'] = DI::l10n()->t('Giants');
	$b['Masochism'] = DI::l10n()->t('Masochism');
	$b['Cars'] = DI::l10n()->t('Cars');
	$b['Menstruation'] = DI::l10n()->t('Menstruation');
	$b['Mucus'] = DI::l10n()->t('Mucus');
	$b['Obscene language'] = DI::l10n()->t('Obscene language');
	$b['Noses'] = DI::l10n()->t('Noses');
	$b['Navels'] = DI::l10n()->t('Navels');
	$b['Corpses'] = DI::l10n()->t('Corpses');
	$b['Smells'] = DI::l10n()->t('Smells');
	$b['Buttocks'] = DI::l10n()->t('Buttocks');
	$b['Nonliving objects'] = DI::l10n()->t('Nonliving objects');
	$b['Sleeping people'] = DI::l10n()->t('Sleeping people');
	$b['Urination'] = DI::l10n()->t('Urination');
	$b['Eating people'] = DI::l10n()->t('Eating people');
	$b['Being eaten'] = DI::l10n()->t('Being eaten');
	$b['Animals'] = DI::l10n()->t('Animals');
	$b['I\'d rather just have some chocolate'] = DI::l10n()->t('I\'d rather just have some chocolate');
}

function morechoice_marital_selector($a,&$b) {
	$b['Married to my job'] = DI::l10n()->t('Married to my job');
	$b['Polygamist'] = DI::l10n()->t('Polygamist');
	$b['Half married'] = DI::l10n()->t('Half married');
	$b['Living in the past'] = DI::l10n()->t('Living in the past');
	$b['Pretending to be over my ex'] = DI::l10n()->t('Pretending to be over my ex');
	$b['Hurt in the past'] = DI::l10n()->t('Hurt in the past');
	$b['Wallowing in self-pity'] = DI::l10n()->t('Wallowing in self-pity');
}
