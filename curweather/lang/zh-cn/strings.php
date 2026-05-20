<?php

if(! function_exists("string_plural_select_zh_CN")) {
function string_plural_select_zh_CN($n){
	$n = intval($n);
	return intval(0);
}}
$a->strings['Error fetching weather data. Error was: '] = '获取天气数据出错。错误是：';
$a->strings['Current Weather'] = '当前天气';
$a->strings['Relative Humidity'] = '相对湿度';
$a->strings['Pressure'] = '压力';
$a->strings['Wind'] = '风';
$a->strings['Last Updated'] = '最后更新';
$a->strings['Data by'] = '数据来自';
$a->strings['Show on map'] = '在地图上显示';
$a->strings['There was a problem accessing the weather data. But have a look'] = '访问天气数据时出现问题。但是看看';
$a->strings['at OpenWeatherMap'] = '在 OpenWeatherMap';
$a->strings['No APPID found, please contact your admin to obtain one.'] = '未找到 APPID，请联系您的管理员获取一个。';
$a->strings['Enter either the name of your location or the zip code.'] = '输入您所在位置的名称或邮政编码。';
$a->strings['Your Location'] = '您的位置';
$a->strings['Identifier of your location (name or zip code), e.g. <em>Berlin,DE</em> or <em>14476,DE</em>.'] = '您所在位置的标识符（名称或邮政编码），例如 <em>Berlin,DE</em>或 <em>14476,DE2。';
$a->strings['Units'] = '单位';
$a->strings['select if the temperature should be displayed in &deg;C or &deg;F'] = '选择是否应以 °C 或 °F 显示温度';
$a->strings['Show weather data'] = '显示天气数据';
$a->strings['Current Weather Settings'] = '当前天气设置';
$a->strings['Save Settings'] = '保存设置';
$a->strings['Caching Interval'] = '缓存间隔';
$a->strings['For how long should the weather data be cached? Choose according your OpenWeatherMap account type.'] = '天气数据应该缓存多长时间？根据您的 OpenWeatherMap 帐户类型进行选择。';
$a->strings['no cache'] = '没有缓存';
$a->strings['minutes'] = '分钟';
$a->strings['Your APPID'] = '您的APPID';
$a->strings['Your API key provided by OpenWeatherMap'] = 'OpenWeatherMap 提供的 API 密钥';
