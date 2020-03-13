$(document).ready(function(){
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

		addHeightToggleHandler($el);
		var limited = processHeightLimit($el);

		if (!limited) {
			var mutationObserver = new MutationObserver(function(mutations) {
				var limited = processHeightLimit($el);
				if (limited) {
					mutationObserver.disconnect()
				}
			});
			mutationObserver.observe($el[0], { attributes: true, characterData: true, childList: true, subtree: true, attributeOldValue: true, characterDataOldValue: true });

			$el.imagesLoaded().then(function(){
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
	$("#" + wrapperId).append('<div class="wall-item-body-toggle" data-item-id="' + itemId + '" id="' + toggleId + '" ><a href="javascript:void(0)" class="wall-item-body-toggle-text">Show more ...</a></div>');
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
	if ($item.height() < 250) {
		$item.removeClass("limit-height");
		$toggle.hide();
		return false;
	} else {
		$item.addClass("limit-height");
		$toggle.show();
		return true;
	}
}


