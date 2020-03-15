var nextBodyIdx = 0;

$(document).ready(function() {
	$("head").append('<style type="text/css"></style>');
	var newStyleElement = $("head").children(':last');
	newStyleElement.html('.limit-height{max-height: ' + postLimitHeight + 'px; overflow: hidden; display:inline-block;}');

	handleNewWallItemBodies();

	document.addEventListener("postprocess_liveupdate", function() {
		handleNewWallItemBodies();
	});
});

function handleNewWallItemBodies() {
	$('.wall-item-body:not(.showmore-done)').each(function() {
		var $el = $(this);
		$el.addClass('showmore-done');
		if ($el.has('button.content-filter-button').length > 0) {
			$el.removeClass('limitable');
			return;
		}

		if (!$el.attr("id")) {
			$el.attr("id", nextBodyIdx++);
		}
		addHeightToggleHandler($el);
		var limited = processHeightLimit($el);

		if (!limited) {
			var mutationObserver = new MutationObserver(function() {
				var limited = processHeightLimit($el);
				if (limited) {
					mutationObserver.disconnect()
				}
			});
			mutationObserver.observe($el[0], {
				attributes: true,
				characterData: true,
				childList: true,
				subtree: true,
				attributeOldValue: true,
				characterDataOldValue: true
			});

			$el.imagesLoaded().then(function() {
				processHeightLimit($el);
			});
		}
	});
}

function addHeightToggleHandler($item) {
	var itemId = parseInt($item.attr("id").replace("wall-item-body-", ""));
	$item.data("item-id", itemId);
	var wrapperId = "wall-item-body-wrapper-" + itemId;
	var toggleId = "wall-item-body-toggle-" + itemId;

	$item.wrap('<div id="' + wrapperId + '" class="wall-item-body-wrapper"></div>');
	$("#" + wrapperId).append('<div class="wall-item-body-toggle" data-item-id="' + itemId + '" id="' + toggleId + '" ><button type="button" class="wall-item-body-toggle-text">' + showmore_dyn_showmore_linktext + '</button></div>');
	$item.addClass("limitable limit-height");

	var $toggle = $("#" + toggleId);
	$toggle.show();
	$toggle.click(function(el) {
		$item.toggleClass("limit-height");
		$(this).hide();
		$item.removeClass("limitable");
	});
}

function processHeightLimit($item) {
	if (!$item.hasClass("limitable")) {
		return false;
	}

	var itemId = $item.data("item-id");
	var $toggle = $("#wall-item-body-toggle-" + itemId);
	if ($item.height() < postLimitHeight) {
		$item.removeClass("limit-height");
		$toggle.hide();
		return false;
	} else {
		$item.addClass("limit-height");
		$toggle.show();
		return true;
	}
}
