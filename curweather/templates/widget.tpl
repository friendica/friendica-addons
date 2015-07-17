<div id="curweather-network" class="widget">
	<div class="title tool">
		<h4 title="{{$lastupdate}}">{{$title}}: {{$city}}</h4>
	</div>
	<p>
	<img src="http://openweathermap.org/img/w/{{$icon}}.png" title="{{$description}}">
	<ul class="curweather-details">
	    <li><strong>{{$temp}}</strong></li>
	    <li>{{$relhumidity['caption']}}: {{$relhumidity['val']}}</li>
	    <li>{{$pressure['caption']}}: {{$pressure['val']}}</li>
	    <li>{{$wind['caption']}}: {{$wind['val']}}</li>
	</ul></p>
	<p class="curweather-footer">
		{{$databy}}: <a	href="http://openweathermap.org">OpenWeatherMap</a>. <a href="http://openweathermap.org/Maps?zoom=7&lat={{$lat}}&lon={{$lon}}&layers=B0FTTFF">{{$showonmap}}</a>
	</p>
</div>
<div class="clear"></div>
