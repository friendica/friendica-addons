<div id="curweather-network" class="widget">
	<div class="title tool clear">
		<h3 title="{{$lastupdate}}">{{$title}}: {{$city}}</h3>
	</div>
	<div class="pull-left">
	<img src="{{$icon}}" title="{{$description}}">
	</div>
	<div class="pull-right">
	<ul class="curweather-details">
		<li><strong>{{$temp}}</strong></li>
		<li>{{$relhumidity['caption']}}: {{$relhumidity['val']}}</li>
		<li>{{$pressure['caption']}}: {{$pressure['val']}}</li>
		<li>{{$wind['caption']}}: {{$wind['val']}}</li>
	</ul>
	</div>
	<div class="clear"></div>
	<div class="curweather-footer pull-left">
		{{$databy}}: <a href="http://openweathermap.org">OpenWeatherMap</a>.
	</div>
	<div class="curweather-footer pull-right">
	<a href="http://openweathermap.org/weathermap?basemap=map&cities=true&layer=temperature&lat={{$lat}}&lon={{$lon}}&zoom=10">{{$showonmap}}</a>
	</div>
</div>
<div class="clear"></div>
