<?php
/**
 * Name: Widgets
 * Description: Allow to embed info from friendica into another site
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrix/>.
 */
function widgets_install()
{
    register_hook('plugin_settings', 'addon/widgets/widgets.php', 'widgets_settings');
    register_hook('plugin_settings_post', 'addon/widgets/widgets.php', 'widgets_settings_post');
    logger('installed widgets');
}
function widgets_uninstall()
{
    unregister_hook('plugin_settings', 'addon/widgets/widgets.php', 'widgets_settings');
    unregister_hook('plugin_settings_post', 'addon/widgets/widgets.php', 'widgets_settings_post');
}

function widgets_settings_post()
{
    if (!local_user()) {
        return;
    }
    if (isset($_POST['widgets-submit'])) {
        del_pconfig(local_user(), 'widgets', 'key');
    }
}

function widgets_settings(&$a, &$o)
{
    if (!local_user()) {
        return;
    }

    $key = get_pconfig(local_user(), 'widgets', 'key');
    if ($key == '') {
        $key = mt_rand();
        set_pconfig(local_user(), 'widgets', 'key', $key);
    }

    $widgets = array();
    $d = dir(dirname(__file__));
    while (false !== ($f = $d->read())) {
        if (substr($f, 0, 7) == 'widget_') {
            preg_match('|widget_([^.]+).php|', $f, $m);
            $w = $m[1];
            if ($w != '') {
                require_once $f;
                $widgets[] = array($w, call_user_func($w.'_widget_name'));
            }
        }
    }

//	$t = file_get_contents( dirname(__file__). "/settings.tpl" );
    $t = get_markup_template('settings.tpl', 'addon/widgets/');
    $o .= replace_macros($t, array(
        '$submit' => t('Generate new key'),
        '$baseurl' => $a->get_baseurl(),
        '$title' => 'Widgets',
        '$label' => t('Widgets key'),
        '$key' => $key,
        '$widgets_h' => t('Widgets available'),
        '$widgets' => $widgets,
    ));
}

function widgets_module()
{
    return;
}

function _abs_url($s)
{
    $a = get_app();

    return preg_replace("|href=(['\"])([^h][^t][^t][^p])|", 'href=$1'.$a->get_baseurl().'/$2', $s);
}

function _randomAlphaNum($length)
{
    return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', $length)), 0, $length);
}

function widgets_content(&$a)
{
    if (!isset($_GET['k'])) {
        if ($a->argv[2] == 'cb') {
            header('HTTP/1.0 400 Bad Request');
            killme();
        }

        return;
    }

    $r = q("SELECT * FROM pconfig WHERE uid IN (SELECT uid FROM pconfig  WHERE v='%s')AND  cat='widgets'",
            dbesc($_GET['k'])
         );
    if (!count($r)) {
        if ($a->argv[2] == 'cb') {
            header('HTTP/1.0 400 Bad Request');
            killme();
        }

        return;
    }
    $conf = array();
    $conf['uid'] = $r[0]['uid'];
    foreach ($r as $e) {
        $conf[$e['k']] = $e['v'];
    }

    $o = '';

    $widgetfile = dirname(__file__).'/widget_'.$a->argv[1].'.php';
    if (file_exists($widgetfile)) {
        require_once $widgetfile;
    } else {
        if ($a->argv[2] == 'cb') {
            header('HTTP/1.0 400 Bad Request');
            killme();
        }

        return;
    }

    //echo "<pre>"; var_dump($a->argv); die();
    if ($a->argv[2] == 'cb') {
        /*header('Access-Control-Allow-Origin: *');*/
        $o .= call_user_func($a->argv[1].'_widget_content', $a, $conf);
    } else {
        if (isset($_GET['p']) && local_user() == $conf['uid']) {
            $o .= '<style>.f9k_widget { float: left;border:1px solid black; }</style>';
            $o .= '<h1>Preview Widget</h1>';
            $o .= '<a href="'.$a->get_baseurl().'/settings/addon">'.t('Plugin Settings').'</a>';

            $o .= '<h4>'.call_user_func($a->argv[1].'_widget_name').'</h4>';
            $o .= call_user_func($a->argv[1].'_widget_help');
            $o .= "<br style='clear:left'/><br/>";
            $o .= '<script>';
        } else {
            header('content-type: application/x-javascript');
        }

        $widget_size = call_user_func($a->argv[1].'_widget_size');

        $script = file_get_contents(dirname(__file__).'/widgets.js');
        $o .= replace_macros($script, array(
            '$entrypoint' => $a->get_baseurl().'/widgets/'.$a->argv[1].'/cb/',
            '$key' => $conf['key'],
            '$widget_id' => 'f9a_'.$a->argv[1].'_'._randomAlphaNum(6),
            '$loader' => $a->get_baseurl().'/images/rotator.gif',
            '$args' => (isset($_GET['a']) ? $_GET['a'] : ''),
            '$width' => $widget_size[0],
            '$height' => $widget_size[1],
            '$type' => $a->argv[1],
        ));

        if (isset($_GET['p'])) {
            $wargs = call_user_func($a->argv[1].'_widget_args');
            $jsargs = implode('</em>,<em>', $wargs);
            if ($jsargs != '') {
                $jsargs = '&a=<em>'.$jsargs.'</em>';
            }

            $o .= "</script>
			<br style='clear:left'/><br/>
			<h4>Copy and paste this code</h4>
			<code>"

            .htmlspecialchars('<script src="'.$a->get_baseurl().'/widgets/'.$a->argv[1].'?k='.$conf['key'])
            .$jsargs
            .htmlspecialchars('"></script>')
            .'</code>';

            return $o;
        }
    }

    echo $o;
    killme();
}
