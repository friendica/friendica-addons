var toolserver = 'http://toolserver.org/~kolossos/openlayers/kml-on-ol.php';
var startTag = '<iframe class="osmFrame" style="width: 100%; height: 350px; clear: both;" src="'+ toolserver + '?lang=de&amp;uselang=de&amp;params=';
var endTag = '"></iframe>';

// @TODO document.ready() does not work for ajax loaded content
jQuery(document).ready(function($) {
	$('.wall-item-content-wrapper').each(function(index) {
		var link = $(this).find('.wall-item-location .OSMMapLink');
		link.toggle(addIframe, removeIframe);
	});
});

function addIframe(ev) {
	var coordinate = $(ev.target).attr('title');
	var newTag = startTag + convertCoordinateString(coordinate) + endTag;
	$(ev.target).parents('.wall-item-content-wrapper').append(newTag);
}

function removeIframe(ev) {
	$(ev.target).parents('.wall-item-content-wrapper').find('iframe').remove();
}

function convertCoordinateString(coordinate) {
	var locstring = coordinate.split(' ');
	var northSouth;
	var westEast;

	if (locstring[0] < 0) {
		northSouth = '_S_';
	}else{
		northSouth = '_N_';
	}
	if (locstring[1] < 0) {
		westEast = '_W';
	}else{
		westEast = '_E';
	}
	return Math.abs(locstring[0]) + northSouth + Math.abs(locstring[1]) + westEast;
}

