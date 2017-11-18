<div id="curweather-network" class="widget">
	<div class="title tool">
		<h4 title="{{$lastupdate}}">{{$title}}: {{$city}}</h4>
	</div>
	<p>
	<img src="{{$icon}}" title="{{$description}}">
	<ul class="curweather-details">
	    <li><strong>{{$temp}}</strong></li>
	    <li>{{$relhumidity['caption']}}: {{$relhumidity['val']}}</li>
	    <li>{{$pressure['caption']}}: {{$pressure['val']}}</li>
	    <li>{{$wind['caption']}}: {{$wind['val']}}</li>
	</ul></p>
	<p class="curweather-footer">
		{{$databy}}: <a	href="http://openweathermap.org">OpenWeatherMap</a>. <a href="http://openweathermap.org/weathermap?basemap=map&cities=true&layer=temperature&lat={{$lat}}&lon={{$lon}}&zoom=9">{{$showonmap}}</a>
	</p>
</div>
<div class="clear"></div>
