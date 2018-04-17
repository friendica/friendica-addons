<div id="adminpage">
	<style>[v-cloak] { display: none; }</style>
	<div id="rules">
		<p><a href="settings/addon">🔙 {{$backtosettings}}</a></p>
		<h1>
			{{$title}}

			<a href="{{$baseurl}}/advancedcontentfilter/help" class="btn btn-default btn-sm" title="{{$help}}">
				<i class="fa fa-question fa-2x" aria-hidden="true"></i>
			</a>
		</h1>
		<div>{{$advanced_content_filter_intro}}</div>
		<h2>
			{{$your_rules}}
			<button class="btn btn-primary btn-sm" title="{{$add_a_rule}}" @click="showModal = true">
				<i class="fa fa-plus fa-2x" aria-hidden="true"></i>
			</button>
		</h2>
		<div v-if="rules.length === 0" v-cloak>
			{{$no_rules}}
		</div>

		<ul class="list-group" v-cloak>
			<li class="list-group-item" v-for="rule in rules">
				<p class="pull-right">
					<button type="button" class="btn btn-xs btn-primary" v-on:click="toggleActive(rule)" aria-label="{{$disable_this_rule}}" title="{{$disable_this_rule}}" v-if="parseInt(rule.active)">
						<i class="fa fa-toggle-on" aria-hidden="true"></i> {{$enabled}}
					</button>
					<button type="button" class="btn btn-xs btn-default" v-on:click="toggleActive(rule)" aria-label="{{$enable_this_rule}}" title="{{$enable_this_rule}}" v-else>
						<i class="fa fa-toggle-off" aria-hidden="true"></i> {{$disabled}}
					</button>

					<button type="button" class="btn btn-xs btn-primary" v-on:click="editRule(rule)" aria-label="{{$edit_this_rule}}" title="{{$edit_this_rule}}">
						<i class="fa fa-pencil" aria-hidden="true"></i>
					</button>
					<button type="button" class="btn btn-xs btn-default" v-on:click="deleteRule(rule)" aria-label="{{$delete_this_rule}}" title="{{$delete_this_rule}}">
						<i class="fa fa-trash-o" aria-hidden="true"></i>
					</button>
				</p>
				<h3 class="list-group-item-heading">
					{{$rule}} #{{ rule.id }}: {{ rule.name }}
				</h3>
				<pre class="list-group-item-text" v-if="rule.expression">{{ rule.expression }}</pre>
			</li>
		</ul>

		<div class="modal fade" ref="vuemodal" tabindex="-1" role="dialog" v-cloak>
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
		{{if current_theme() == 'frio'}}
						<button type="button" class="close" data-dismiss="modal" aria-label="{{$close}}" @click="showModal = false"><span aria-hidden="true">&times;</span></button>
		{{/if}}
						<h3 v-if="rule.id">{{$edit_the_rule}} "{{ rule.name }}"</h3>
						<h3 v-if="!rule.id">{{$add_a_rule}}</h3>
					</div>
					<div class="modal-body">
						<form>
							<input type="hidden" name="form_security_token" id="csrf" value="{{$form_security_token}}" />
							<div class="alert alert-danger" role="alert" v-if="errorMessage">{{ errorMessage }}</div>
							<div class="form-group">
								<input class="form-control" placeholder="{{$rule_name}}" v-model="rule.name">
							</div>
							<div class="form-group">
								<input class="form-control" placeholder="{{$rule_expression}}" v-model="rule.expression">
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close" @click="resetForm()">{{$cancel}}</button>
						<button slot="button" class="btn btn-primary" type="button" v-if="rule.id" v-on:click="saveRule(rule)">{{$save_this_rule}}</button>
						<button slot="button" class="btn btn-primary" type="button" v-if="!rule.id" v-on:click="addRule()">{{$add_a_rule}}</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<form class="form-inline" v-on:submit.prevent="showVariables()">
			<fieldset>
				<legend>Show post variables</legend>
				<div class="form-group" style="width: 50%">
					<label for="itemUrl" class="sr-only">Post URL or item guid</label>
					<input class="form-control" id="itemUrl" placeholder="Post URL or item guid" v-model="itemUrl" style="width: 100%">
				</div>
				<button type="submit" class="btn btn-primary">Show Variables</button>
			</fieldset>
		</form>
		<pre>
{{ itemJson }}
		</pre>
	</div>

	<script> var existingRules = {{$rules}};</script>

	<!-- JS -->
	<script src="{{$baseurl}}/addon/advancedcontentfilter/vendor/asset/vue/dist/vue.min.js"></script>
	<script src="{{$baseurl}}/addon/advancedcontentfilter/vendor/asset/vue-resource/dist/vue-resource.min.js"></script>
	<script src="{{$baseurl}}/addon/advancedcontentfilter/advancedcontentfilter.js"></script>
</div>
