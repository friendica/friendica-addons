<!-- <link rel="stylesheet" type="text/css" href="/addon/cookienotice/css/cookienotice.css" /> -->
<script>
	window.addEventListener("load", function () {
		var cookiename = 'cncookiesaccepted'
		var cookie = getCookie(cookiename);
		
		if (cookie == "") {
			document.getElementById('cookienotice-box').style.display = 'block';
			document.getElementById('cookienotice-ok-button').onclick = function () {
				console.log('clicked');
				setCookie(cookiename, 1, 365);
				document.getElementById('cookienotice-box').style.display = 'none';
			};
		}
		
		function setCookie(cname, cvalue, exdays) {
			var d = new Date();
			d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			var expires = "expires=" + d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}
		
		function getCookie(cname) {
			var name = cname + "=";
			var decodedCookie = decodeURIComponent(document.cookie);
			var ca = decodedCookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) == 0) {
					return c.substring(name.length, c.length);
				}
			}
			return "";
		}

	});
</script>
