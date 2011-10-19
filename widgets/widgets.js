/**
 * @author Fabio Comuni
 */
var f9a_widget_$widget_id = {
	width: "$width",
	height: "$height",
	entrypoint : "$entrypoint",
	key	: "$key",
	widgetid: "$widget_id",
	argstr: "$args",

	load : function() {
		var args = new Array();
		args['k']=this.key;
		args['s']=window.location.href;
		args['a']=this.argstr;
		var urlencodedargs = new Array();
		for(k in args){ 
			if (typeof args[k] != 'function')
				urlencodedargs.push( encodeURIComponent(k)+"="+encodeURIComponent(args[k]) );
		 }
		var url = this.entrypoint + "?"+ urlencodedargs.join("&");

		console.log(this.widgetid);
		console.log(document.getElementById(this.widgetid));
		document.getElementById(this.widgetid).innerHTML = '<iframe style="border:0px; width: '+this.width+'; height:'+this.height+'" src="'+url+'"></iframe>';
	}

};

document.writeln("<div id='$widget_id' class='f9k_widget $type'>");
document.writeln("<img id='$widget_id_ld' src='$loader'>");
document.writeln("</div>");
(function() {
	f9a_widget_$widget_id.load();	
})();

