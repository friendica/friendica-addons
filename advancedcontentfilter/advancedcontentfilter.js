Vue.http.headers.common['X-CSRF-Token'] = document.querySelector('#csrf').getAttribute('value');

new Vue({
	el: '#rules',

	data: {
		showModal: false,
		errorMessage: '',
		editedIndex: null,
		rule: {id: '', name: '', expression: '', created: ''},
		rules: existingRules || [],
		itemUrl: '',
		itemJson: ''
	},

	watch: {
		showModal: function () {
			if (this.showModal) {
				$(this.$refs.vuemodal).modal('show');
			} else {
				$(this.$refs.vuemodal).modal('hide');
			}
		}
	},

	//created: function () {
	//	this.fetchRules();
	//},

	methods: {
		resetForm: function() {
			this.rule = {id: '', name: '', expression: '', created: ''};
			this.showModal = false;
			this.editedIndex = null;
		},

		//fetchRules: function () {
		//	this.$http.get('/advancedcontentfilter/api/rules')
		//	.then(function (response) {
		//		this.rules = response.body;
		//	}, function (err) {
		//		console.log(err);
		//	});
		//},

		addRule: function () {
			if (this.rule.name.trim()) {
				this.errorMessage = '';
				this.$http.post('/advancedcontentfilter/api/rules', this.rule)
				.then(function (res) {
					this.rules.push(res.body.rule);
					this.resetForm();
				}, function (err) {
					this.errorMessage = err.body.message;
				});
			}
		},

		editRule: function (rule) {
			this.editedIndex = this.rules.indexOf(rule);
			this.rule = Object.assign({}, rule);
			this.showModal = true;
		},

		saveRule: function (rule) {
			this.errorMessage = '';
			this.$http.put('/advancedcontentfilter/api/rules/' + rule.id, rule)
			.then(function (res) {
				this.rules[this.editedIndex] = rule;
				this.resetForm();
			}, function (err) {
				this.errorMessage = err.body.message;
			});
		},

		toggleActive: function (rule) {
			this.$http.put('/advancedcontentfilter/api/rules/' + rule.id, {'active': Math.abs(parseInt(rule.active) - 1)})
			.then(function (res) {
				this.rules[this.rules.indexOf(rule)].active = Math.abs(parseInt(rule.active) - 1);
			}, function (err) {
				console.log(err);
			});
		},

		deleteRule: function (rule) {
			if (confirm('Are you sure you want to delete this rule?')) {
				this.$http.delete('/advancedcontentfilter/api/rules/' + rule.id)
				.then(function (res) {
					this.rules.splice(this.rules.indexOf(rule), 1);
				}, function (err) {
					console.log(err);
				});
			}
		},

		showVariables: function () {
			var guid = '';

			var urlParts = this.itemUrl.split('/');

			guid = urlParts[urlParts.length - 1];

			this.$http.get('/advancedcontentfilter/api/variables/' + guid)
			.then(function (response) {
				this.itemJson = response.bodyText;
			}, function (err) {
				console.log(err);
			});

			return false;
		}
	}
});