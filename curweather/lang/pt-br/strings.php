<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Current Weather'] = 'Clima atual';
$a->strings['Relative Humidity'] = 'Umidade relativa';
$a->strings['Pressure'] = 'Pressão';
$a->strings['Wind'] = 'Vento';
$a->strings['Last Updated'] = 'Atualizado';
$a->strings['Data by'] = 'Dados de';
$a->strings['Show on map'] = 'Mostrar no mapa';
$a->strings['There was a problem accessing the weather data. But have a look'] = 'Houve um problema no acesso aos dados do clima. Mas dê uma olhada';
$a->strings['at OpenWeatherMap'] = 'em OpenWeatherMap';
$a->strings['Enter either the name of your location or the zip code.'] = 'Informe sua localização ou seu CEP.';
$a->strings['Your Location'] = 'Sua localização';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = 'Identificador da sua localização (nome ou CEP), p.ex. <em>Rio,BR</em> ou <em>20021,BR</em>.';
$a->strings['Units'] = 'Unidades';
$a->strings['Show weather data'] = 'Mostrar dados do clima';
$a->strings['Save Settings'] = 'Salvar Configurações';
$a->strings['Caching Interval'] = 'Intervalo de cache';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = 'Por quanto tempo os dados do clima devem ser guardados em cache? Escolha de acordo com o tipo da sua conta no OpenWeatherMap.';
$a->strings['no cache'] = 'sem cache';
$a->strings['minutes'] = 'minutos';
$a->strings['Your APPID'] = 'Seu AppID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'Sua chave de API fornecida pelo OpenWeatherMap';
