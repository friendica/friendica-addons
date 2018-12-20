hljs.initHighlightingOnLoad();

document.addEventListener('postprocess_liveupdate', function () {
	var blocks = document.querySelectorAll('pre code:not(.hljs)');
	Array.prototype.forEach.call(blocks, hljs.highlightBlock);
});
