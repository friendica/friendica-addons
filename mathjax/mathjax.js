document.addEventListener('postprocess_liveupdate', function () {
	MathJax.Hub.Queue(['Typeset', MathJax.Hub]);
});
