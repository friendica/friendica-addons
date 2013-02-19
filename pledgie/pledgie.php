<?php
/**
 *  * Name: Pledgie
 *   * Description: Show link to Friendica pledgie account for donating
 *    * Version: 1.0
 *     * Author: tony baldwin <tony@free-haven.org>
 *      */


function pledgie_install() { register_hook('page_end', 'addon/pledgie/pledgie.php', 'pledgie_active'); }


function pledgie_uninstall() { unregister_hook('page_end', 'addon/pledgie/pledgie.php', 'pledgie_active'); }

function pledgie_active(&$a,&$b) { $b .= '<div style="position: fixed; bottom: 25px; left: 5px;"><a href=\'http://www.pledgie.com/campaigns/18417\'><img alt=\'Click here to lend your support to: Beyond Social Networking and make a donation at www.pledgie.com !\' src=\'http://www.pledgie.com/campaigns/18417.png?skin_name=chrome\' border=\'0\' target=\'_blank\' /></a></div>'; } 

