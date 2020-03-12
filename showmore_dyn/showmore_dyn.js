$(document).ready(function(){
        handleNewWallItemBodies();

        var mutationObserver = new MutationObserver(function(mutations) {
                handleNewWallItemBodies();
        });
        mutationObserver.observe($("#content")[0], { attributes: false, characterData: false, childList: true, subtree: true, attributeOldValue: false, characterDataOldValue: false });
});

function handleNewWallItemBodies() {
        $('.wall-item-body:not(.showmore-done)').each(function(i, el) {
                $(el).addClass('showmore-done');
                if ($(el).has('button.content-filter-button').length > 0) {
                        $(el).removeClass('limitable');
                        return;
                }

                var itemId = $(el).attr('id');
                addHeightToggleHandler(itemId);
                var limited = processHeightLimit(itemId);

                if (!limited) {
                        var mutationObserver = new MutationObserver(function(mutations) {
                                var limited = processHeightLimit(itemId);
                                if (limited) {
                                        mutationObserver.disconnect()
                                }
                        });
                        mutationObserver.observe(el, { attributes: true, characterData: true, childList: true, subtree: true, attributeOldValue: true, characterDataOldValue: true });

                        $(el).imagesLoaded().then(function(){
                                processHeightLimit(itemId);
                        });
                }
        });
}

function addHeightToggleHandler(id) {
        var itemIdSel = "#" + id;
        var itemId = parseInt(id.replace("wall-item-body-", ""));
        $(itemIdSel).data("item-id", itemId);
        var wrapperId = "wall-item-body-wrapper-" + itemId;
        var wrapperIdSel = "#" + wrapperId;
        var toggleId = "wall-item-body-toggle-" + itemId;
        var toggleIdSel = "#" + toggleId;

        $(itemIdSel).wrap('<div id="' + wrapperId + '" class="wall-item-body-wrapper"></div>');
        $(wrapperIdSel).append('<div class="wall-item-body-toggle" data-item-id="' + itemId + '" id="' + toggleId + '" ><a href="javascript:void(0)" class="wall-item-body-toggle-text">Show more ...</a></div>');

        $(toggleIdSel).show();
        $(toggleIdSel).click(function(el) {
                $(itemIdSel).toggleClass("limit-height");
                $(this).hide();
                $(itemIdSel).removeClass("limitable");
        });
}

function processHeightLimit(id) {
        var idSel = "#" + id;

        if (!$(idSel).hasClass("limitable")) {
                return false;
        }

        var itemId = $(idSel).data("item-id");
        var toggleSelector = "#wall-item-body-toggle-" + itemId;
        if ($(idSel).height() < 250) {
                $(idSel).removeClass("limit-height");
                $(toggleSelector).hide();
                return false;
        } else {
                $(idSel).addClass("limit-height");
                $(toggleSelector).show();
                return true;
        }
}


