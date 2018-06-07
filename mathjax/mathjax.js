Addon_registerHook("postprocess","mathjax_postprocess_liveupdate");
function mathjax_postprocess_liveupdate()
{
	MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
}
