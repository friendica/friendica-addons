Leistungsschutzrecht Addon
==========================

Main author: Michael Vogel

This addon handles legal problems with the German link tax, named "Leistungsschutzrecht" by shortening preview texts.
Additionally, it is possibly to suppress preview pictures completely to avoid any legal problems.

## Configuration

If you want to suppress pictures in previews, add this to your global `config/leistungsschutzrecht.config.php`:

	return [
		'leistungsschutzrecht' => [
			'suppress_photos' => true,
		],
	];
