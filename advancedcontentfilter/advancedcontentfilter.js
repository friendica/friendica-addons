$.ajaxSetup({headers: {'X-CSRF-Token': document.querySelector('#csrf').getAttribute('value')}});

$.extend({
	ajaxJSON: function(method, url, data) {
		return $.ajax({
			type: method.toUpperCase(),
			url: url,
			data: JSON.stringify(data),
			contentType: 'application/json; charset=utf-8',
			dataType: 'json'
		});
	}
});

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

	methods: {
		resetForm: function() {
			this.rule = {id: '', name: '', expression: '', created: ''};
			this.showModal = false;
			this.editedIndex = null;
		},

		addRule: function () {
			if (this.rule.name.trim()) {
				this.errorMessage = '';

				var self = this;
				$.ajaxJSON('post', '/advancedcontentfilter/api/rules', this.rule)
				.then(function (responseJSON) {
					self.rules.push(responseJSON.rule);
					self.resetForm();
				}, function (response) {
					self.errorMessage = response.responseJSON.message;
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

			var self = this;
			$.ajaxJSON('put', '/advancedcontentfilter/api/rules/' + rule.id, rule)
			.then(function () {
				self.rules[self.editedIndex] = rule;
				self.resetForm();
			}, function (response) {
				self.errorMessage = response.responseJSON.message;
			});
		},

		toggleActive: function (rule) {
			var previousValue = this.rules[this.rules.indexOf(rule)].active;
			var newValue = Math.abs(parseInt(rule.active) - 1);

			this.rules[this.rules.indexOf(rule)].active = newValue;

			var self = this;
			$.ajaxJSON('put', '/advancedcontentfilter/api/rules/' + rule.id, {'active': newValue})
			.fail(function (response) {
				self.rules[self.rules.indexOf(rule)].active = previousValue;
				console.log(response.responseJSON.message);
			});
		},

		deleteRule: function (rule) {
			if (confirm('Are you sure you want to delete this rule?')) {
				var self = this;
				$.ajaxJSON('delete', '/advancedcontentfilter/api/rules/' + rule.id)
				.then(function () {
					self.rules.splice(self.rules.indexOf(rule), 1);
				}, function (response) {
					console.log(response.responseJSON.message);
				});
			}
		},

		showVariables: function () {
			var urlParts = this.itemUrl.split('/');
			var guid = urlParts[urlParts.length - 1];

			this.itemJson = '';

			var self = this;
			$.ajaxJSON('get', '/advancedcontentfilter/api/variables/' + guid)
			.then(function (responseJSON) {
				self.itemJson = responseJSON.variables;
			}, function (response) {
				self.itemJson = response.responseJSON.message;
			});

			return false;
		}
	}
});