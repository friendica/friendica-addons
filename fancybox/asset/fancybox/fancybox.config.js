$(document).ready(function() {
    $.fancybox.defaults.loop = "true";
    // this disables the colorbox hook found in frio/js/modal.js:34
    $("body").off("click", ".wall-item-body a img");

    // Adds ALT/TITLE text to fancybox
    $('a[data-fancybox').fancybox({
        afterLoad : function(instance, current) {
            current.$image.attr('alt', current.opts.$orig.find('img').attr('alt') );
            current.$image.attr('title', current.opts.$orig.find('img').attr('title') );
        }
    });
});