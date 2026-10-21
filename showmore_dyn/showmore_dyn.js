var nextBodyIdx = 0;
var showmoreDynLiveUpdateBound = false;

// ready() only fires once for this script, but SPA navigation swaps in a new
// timeline without it firing again, so onDocumentReady() is needed to redo
// the height limiting on every timeline the user navigates to.
window.onDocumentReady('body', function() {
	loc = window.location.pathname;
	if (loc.startsWith('/display')) {
		return;
	}

	if (!postLimitHeight) {
		return;
	}

	if (!document.getElementById('showmore-dyn-style')) {
		$('head').append('<style type="text/css" id="showmore-dyn-style">.limit-height{max-height: ' + postLimitHeight + 'px; overflow: hidden; }</style>');
	}

	handleNewWallItemBodies();

	if (!showmoreDynLiveUpdateBound) {
		showmoreDynLiveUpdateBound = true;
		document.addEventListener('postprocess_liveupdate', function() {
			handleNewWallItemBodies();
		});
	}
});

// Bodies the user expanded, keyed by uri-id and kept for the tab so a live update
// or a return to the timeline doesn't collapse them again
function expandedBodies() {
	try {
		return JSON.parse(sessionStorage.getItem('showmore_dyn_expanded')) || {};
	} catch (e) {
		return {};
	}
}

function storeExpandedBodies(expanded) {
	try {
		sessionStorage.setItem('showmore_dyn_expanded', JSON.stringify(expanded));
	} catch (e) {}
}

function rememberExpandedBody($item) {
	var uriId = $item.closest('[data-uri-id]').attr('data-uri-id');
	if (!uriId) {
		return;
	}

	var expanded = expandedBodies();
	expanded[uriId] = true;
	storeExpandedBodies(expanded);
}

// Drop remembered bodies whose thread is no longer loaded on the page
function pruneExpandedBodies() {
	var expanded = expandedBodies();
	var present = {};
	$('[data-uri-id]').each(function() {
		present[this.getAttribute('data-uri-id')] = true;
	});

	var changed = false;
	Object.keys(expanded).forEach(function(uriId) {
		if (!present[uriId]) {
			delete expanded[uriId];
			changed = true;
		}
	});

	if (changed) {
		storeExpandedBodies(expanded);
	}
}

function handleNewWallItemBodies() {
	var expanded = expandedBodies();

	$('.wall-item-body:not(.showmore-done)').each(function() {
		var $el = $(this);
		$el.addClass('showmore-done');

		if (expanded[$el.closest('[data-uri-id]').attr('data-uri-id')]) {
			$el.removeClass('limitable limit-height');
			return;
		}

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

	pruneExpandedBodies();
}

function addHeightToggleHandler($item) {
	var itemId = parseInt($item.attr("id").replace("wall-item-body-", ""));
	$item.data("item-id", itemId);
	var toggleId = "wall-item-body-toggle-" + itemId;

	$item.append('<div class="wall-item-body-toggle" data-item-id="' + itemId + '" id="' + toggleId + '" ><button type="button" class="wall-item-body-toggle-text" aria-hidden="true">' + showmore_dyn_showmore_linktext + '</button></div>');
	$item.addClass("limitable limit-height");

	var $toggle = $("#" + toggleId);
	$toggle.show();
	$toggle.click(function(el) {
		$item.toggleClass("limit-height");
		$(this).hide();
		$item.removeClass("limitable");
		rememberExpandedBody($item);
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
