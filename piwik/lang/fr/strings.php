<?php

if (!function_exists('string_plural_select_fr')) {
    function string_plural_select_fr($n)
    {
        return $n > 1;
    }
}

$a->strings["This website is tracked using the <a href='http://www.piwik.org'>Piwik</a> analytics tool."] = "Ce site web utilise <a href='http://www.piwik.org'>Piwik</a> en tant qu'outil d'analyses.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Piwik from tracking further visits of the site</a> (opt-out)."] = '';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Piwik Base URL'] = 'URL de base de Piwik';
$a->strings['Absolute path to your Piwik installation. (without protocol (http/s), with trailing slash)'] = '';
$a->strings['Site ID'] = 'ID du site';
$a->strings['Show opt-out cookie link?'] = "Montrer le lien d'opt-out pour les cookies ?";
$a->strings['Asynchronous tracking'] = 'Suivi asynchrone';
$a->strings['Settings updated.'] = 'Paramètres mis à jour.';
