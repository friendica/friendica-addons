<?php
/**
 * Name: Widgets
 * Description: Allow to embed info from friendica into another site
 * Version: 1.0
 * Author: Fabio Comuni <http://kirgroup.com/profile/fabrix/>
 * Status: Unsupported
 */

use Friendica\Core\Hook;
use Friendica\Core\L10n;
use Friendica\Core\Logger;
use Friendica\Core\Renderer;
use Friendica\Database\DBA;
use Friendica\DI;

function widgets_install() {
	Hook::register('addon_settings', 'addon/widgets/widgets.php', 'widgets_settings');
	Hook::register('addon_settings_post', 'addon/widgets/widgets.php', 'widgets_settings_post');
	Logger::log("installed widgets");
}

function widgets_uninstall() {
	Hook::unregister('addon_settings', 'addon/widgets/widgets.php', 'widgets_settings');
	Hook::unregister('addon_settings_post', 'addon/widgets/widgets.php', 'widgets_settings_post');
}

function widgets_settings_post(){
	if(! local_user())
		return;
	if (isset($_POST['widgets-submit'])){
		DI::pConfig()->delete(local_user(), 'widgets', 'key');

	}
}

function widgets_settings(&$a,&$o) {
    if(! local_user())
		return;


	$key = DI::pConfig()->get(local_user(), 'widgets', 'key' );
	if ($key=='') { $key = mt_rand(); DI::pConfig()->set(local_user(), 'widgets', 'key', $key); }

	$widgets = [];
	$d = dir(dirname(__file__));
	while(false !== ($f = $d->read())) {
		 if(substr($f,0,7)=="widget_") {
			 preg_match("|widget_([^.]+).php|", $f, $m);
			 $w=$m[1];
			 if ($w!=""){
				require_once($f);
				$widgets[] = [$w, call_user_func($w."_widget_name")];
			}

		 }
	}



#	$t = file_get_contents( dirname(__file__). "/settings.tpl" );
	$t = Renderer::getMarkupTemplate("settings.tpl", "addon/widgets/");
	$o .= Renderer::replaceMacros($t, [
		'$submit' => DI::l10n()->t('Generate new key'),
		'$title' => "Widgets",
		'$label' => DI::l10n()->t('Widgets key'),
		'$key' => $key,
		'$widgets_h' => DI::l10n()->t('Widgets available'),
		'$widgets' => $widgets,
	]);

}

function widgets_module() {
	return;
}

function _abs_url($s){
	return preg_replace("|href=(['\"])([^h][^t][^t][^p])|", "href=\$1" . DI::baseUrl()->get() . "/\$2", $s);
}

function _randomAlphaNum($length){
	return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',$length)),0,$length);
}


function widgets_content(&$a) {

	if (!isset($_GET['k'])) {
		if($a->argv[2]=="cb"){header('HTTP/1.0 400 Bad Request'); exit();}
		return;
	}

	$r = q("SELECT * FROM pconfig WHERE uid IN (SELECT uid FROM pconfig  WHERE v='%s')AND  cat='widgets'",
			DBA::escape($_GET['k'])
		 );
	if (!count($r)){
		if($a->argv[2]=="cb"){header('HTTP/1.0 400 Bad Request'); exit();}
		return;
	}
	$conf = [];
	$conf['uid'] = $r[0]['uid'];
	foreach($r as $e) { $conf[$e['k']]=$e['v']; }

	$o = "";

	$widgetfile =dirname(__file__)."/widget_".$a->argv[1].".php";
	if (file_exists($widgetfile)){
		require_once($widgetfile);
	} else {
		if($a->argv[2]=="cb"){header('HTTP/1.0 400 Bad Request'); exit();}
		return;
	}




	//echo "<pre>"; var_dump($a->argv); die();
	if ($a->argv[2]=="cb"){
		/*header('Access-Control-Allow-Origin: *');*/
		$o .= call_user_func($a->argv[1].'_widget_content',$a, $conf);

	} else {


		if (isset($_GET['p']) && local_user()==$conf['uid'] ) {
			$o .= "<style>.f9k_widget { float: left;border:1px solid black; }</style>";
			$o .= "<h1>Preview Widget</h1>";
			$o .= '<a href="'.DI::baseUrl()->get().'/settings/addon">'. DI::l10n()->t("Addon Settings") .'</a>';

			$o .=  "<h4>".call_user_func($a->argv[1].'_widget_name')."</h4>";
			$o .=  call_user_func($a->argv[1].'_widget_help');
			$o .= "<br style='clear:left'/><br/>";
			$o .= "<script>";
		} else {
			header("content-type: application/x-javascript");
		}



		$widget_size = call_user_func($a->argv[1].'_widget_size');

		$script = file_get_contents(dirname(__file__)."/widgets.js");
		$o .= Renderer::replaceMacros($script, [
			'$entrypoint' => DI::baseUrl()->get()."/widgets/".$a->argv[1]."/cb/",
			'$key' => $conf['key'],
			'$widget_id' => 'f9a_'.$a->argv[1]."_"._randomAlphaNum(6),
			'$loader' => DI::baseUrl()->get()."/images/rotator.gif",
			'$args' => (isset($_GET['a'])?$_GET['a']:''),
			'$width' => $widget_size[0],
			'$height' => $widget_size[1],
			'$type' => $a->argv[1],
		]);


		if (isset($_GET['p'])) {
			$wargs = call_user_func($a->argv[1].'_widget_args');
			$jsargs = implode("</em>,<em>", $wargs);
			if ($jsargs!='') $jsargs = "&a=<em>".$jsargs."</em>";

			$o .= "</script>
			<br style='clear:left'/><br/>
			<h4>Copy and paste this code</h4>
			<code>"

			.htmlspecialchars('<script src="'.DI::baseUrl()->get().'/widgets/'.$a->argv[1].'?k='.$conf['key'])
			.$jsargs
			.htmlspecialchars('"></script>')
			."</code>";


			return $o;
		}
	}

	echo $o;
	exit();
}
