<?php
/**
 * Name: More Choice
 * Description: Additional gender/sexual preference/marital status options
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
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

}

// We aren't going to bother translating these to other languages. 

function morechoice_gender_selector($a,&$b) {
	if($a->config['system']['language'] == 'en') {
		$b[] = 'Trigender';


	}
}

function morechoice_sexpref_selector($a,&$b) {
	if($a->config['system']['language'] == 'en') {
		$b[] = 'Girls with big tits';
		$b[] = 'Millionaires';
		$b[] = 'Guys with big schlongs';
		$b[] = 'Easy women';

	}
}

function morechoice_marital_selector($a,&$b) {
	if($a->config['system']['language'] == 'en') {
		$b[] = 'Married to my job';
		$b[] = 'Polygamist';

	}
}
