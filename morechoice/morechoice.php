<?php
/**
 * Name: More Choice
 * Description: Additional gender/sexual preference/marital status options
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 *    - who takes no responsibility for any additional content which may appear herein
 *
 */


function morechoice_install() {

	register_hook('gender_selector', 'addon/morechoice/morechoice.php', 'morechoice_gender_selector');
	register_hook('sexpref_selector', 'addon/morechoice/morechoice.php', 'morechoice_sexpref_selector');
	register_hook('marital_selector', 'addon/morechoice/morechoice.php', 'morechoice_marital_selector');
}


function morechoice_uninstall() {

	unregister_hook('gender_selector', 'addon/morechoice/morechoice.php', 'morechoice_gender_selector');
	unregister_hook('sexpref_selector', 'addon/morechoice/morechoice.php', 'morechoice_sexpref_selector');
	unregister_hook('marital_selector', 'addon/morechoice/morechoice.php', 'morechoice_marital_selector');

// We need to leave this here for a while, because we now have a situation where people can end up with an orphaned hook.
	unregister_hook('poke_verbs', 'addon/morechoice/morechoice.php', 'morechoice_poke_verbs');

}

// We aren't going to bother translating these to other languages. 

function morechoice_gender_selector($a,&$b) {
	if($a->config['system']['language'] == 'en') {
		$b[] = 'Androgyne';
		$b[] = 'Bear';	
		$b[] = 'Bigender';	
		$b[] = 'Cross dresser';
		$b[] = 'Drag queen';
		$b[] = 'Eunuch';
		$b[] = 'Faux queen';	
		$b[] = 'Gender fluid';
		$b[] = 'Kathoey';
		$b[] = 'Lady';
		$b[] = 'Lipstick lesbian';
		$b[] = 'Metrosexual';
		$b[] = 'Monk';
		$b[] = 'Nun';
		$b[] = 'Soft butch';
		$b[] = 'Stone femme';
		$b[] = 'Tomboy';
		$b[] = 'Transman';
		$b[] = 'Transwoman';
		$b[] = 'Transvesti';
		$b[] = 'Trigender';
		$b[] = 'Can\'t remember';
		$b[] = 'Hard to tell these days';
	}
}

function morechoice_sexpref_selector($a,&$b) {
	if($a->config['system']['language'] == 'en') {
		$b[] = 'Girls with big tits';
		$b[] = 'Millionaires';
		$b[] = 'Guys with big schlongs';
		$b[] = 'Easy women';
		$b[] = 'People with impaired mobility';
		$b[] = 'Amputees';
		$b[] = 'Statues, mannequins and immobility';
		$b[] = 'Pain';
		$b[] = 'Trans men';
		$b[] = 'Older women';
		$b[] = 'Asphyxiation';
		$b[] = 'In public';
		$b[] = 'In danger';
		$b[] = 'Pretending to be male';
		$b[] = 'Pretending to be female';
		$b[] = 'Breats';
		$b[] = 'Scat';
		$b[] = 'Crying';
		$b[] = 'Nappies/Diapers';
		$b[] = 'Trees';
		$b[] = 'Vomit';
		$b[] = 'Murder';
		$b[] = 'Fat people';
		$b[] = 'Feet';
		$b[] = 'Covered in insects';
		$b[] = 'Turning a human being into furniture';
		$b[] = 'Elderly people';
		$b[] = 'Transgender people';
		$b[] = 'Criminals';
		$b[] = 'Stealing';
		$b[] = 'Breast milk';
		$b[] = 'Immersing genitals in liquids';
		$b[] = 'Giants';
		$b[] = 'Masochism';
		$b[] = 'Cars';
		$b[] = 'Menstruation';
		$b[] = 'Mucus';
		$b[] = 'Obscene language';
		$b[] = 'Noses';
		$b[] = 'Navels';
		$b[] = 'Corpses';
		$b[] = 'Smells';
		$b[] = 'Buttocks';
		$b[] = 'Nonliving objects';
		$b[] = 'Sleeping people';
		$b[] = 'Urination';
		$b[] = 'Eating people';
		$b[] = 'Being eaten';
		$b[] = 'Animals';
		$b[] = 'I\'d rather just have some chocolate';
	}
}

function morechoice_marital_selector($a,&$b) {
	if($a->config['system']['language'] == 'en') {
		$b[] = 'Married to my job';
		$b[] = 'Polygamist';
		$b[] = 'Half married';
		$b[] = 'Living in the past';
		$b[] = 'Pretending to be over my ex';
		$b[] = 'Hurt in the past';
		$b[] = 'Wallowing in self-pity';
	}
}