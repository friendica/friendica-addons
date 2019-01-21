Leistungsschutzrecht Addon
==========================

Main author Michael Vogel

This addon handles legal problems with the German link tax, named "Leistungsschutzrecht" by shortening preview texts.
Additionally it is possibly to suppress preview pictures completely to avoid any legal problems.

## configuration

If you want to suppress pictures in previews, add this to your global `config/addon.config.php`:

	'leistungsschutzrecht' => [
		'suppress_photos' => true,
	],

