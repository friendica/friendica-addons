$(document).ready(function() {
    $.fancybox.defaults.loop = "true";
    // this disables the colorbox hook found in frio/js/modal.js:34
    $("body").off("click", ".wall-item-body a img");
});