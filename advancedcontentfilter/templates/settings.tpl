<div id="adminpage">
	<style>[v-cloak] { display: none; }</style>
	<div id="rules"></div>

	<script>
		var existingRules = {{$rules nofilter}};

		var messages = {
	{{foreach $messages as $key => $value}}
			{{$key}}: "{{$value}}",
	{{/foreach}}
		};

		var csrfToken = "{{$form_security_token}}";

		var currentTheme = "{{$current_theme}}";
	</script>

	<!-- JS -->
	<script src="{{$baseurl}}/view/asset/vue/dist/vue.min.js"></script>
	<script src="{{$baseurl}}/addon/advancedcontentfilter/advancedcontentfilter.js"></script>
</div>
