<?php

require_once("boot.php");

if(@is_null($a)) {
	$a = new App;
}

if(is_null($db)) {
	@include(".htconfig.php");
	require_once("dba.php");
	$db = new dba($db_host, $db_user, $db_pass, $db_data);
	unset($db_host, $db_user, $db_pass, $db_data);
};

$a->set_baseurl(get_config('system','url'));

require_once("addon/geocoordinates/geocoordinates.php");

function geocoordinates_resolve_item2(&$item) {
	if((!$item["coord"]) || ($item["location"]))
                return;

	$key = get_config("geocoordinates", "api_key");
	if ($key == "")
		return;

	$language = get_config("geocoordinates", "language");
	if ($language == "")
		$language = "de";

        $result = Cache::get("geocoordinates:".$language.":".$item["coord"]);
        if (!is_null($result)) {
		$item["location"] = $result;
		return;
        }

        $coords = explode(' ',$item["coord"]);

        $s = fetch_url("https://api.opencagedata.com/geocode/v1/json?q=".$coords[0].",".$coords[1]."&key=".$key."&language=".$language);

	if (!$s) {
		logger("API could not be queried", LOGGER_DEBUG);
		return;
	}

	$data = json_decode($s);

	if ($data->status->code != "200") {
		logger("API returned error ".$data->status->code." ".$data->status->message, LOGGER_DEBUG);
		return;
	}

	if (($data->total_results == 0) OR (count($data->results) == 0)) {
		logger("No results found", LOGGER_DEBUG);
		return;
	}

	$item["location"] = $data->results[0]->formatted;

        Cache::set("geocoordinates:".$language.":".$item["coord"], $item["location"]);

}

$r = q("SELECT coord, location FROM item WHERE guid='stat1721635584fdaf31b19541063667' LIMIT 1");
$item = $r[0];
geocoordinates_resolve_item2($item);
print_r($item);
?>
