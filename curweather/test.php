 <?php 
require_once  'getweather.php';
$rpt = "KHVN";
$wxdata = GetWeather::get($rpt);
var_dump($rpt, $wxdata);