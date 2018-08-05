<!--
	This the the source HTML for the render functions defined in advancedcontentfilter.js
	This file is only for reference only and editing it won't change the addon display.
	Here's the workflow to change the actual display:
	1. Edit this file
	2. Run it through https://vuejs.org/v2/guide/render-function.html#Template-Compilation
	3. Replace the render and staticRenderFns members in advancedcontentfilter.js by the contents of the anonymous() functions
-->
<div id="rules">
	<p><a href="settings/addon">🔙 {{ messages.backtosettings }}</a></p>
	<h1>
		{{ messages.title }}
		&nbsp;
		<a href="advancedcontentfilter/help" class="btn btn-default btn-sm" :title="messages.help">
			<i class="fa fa-question fa-2x" aria-hidden="true"></i>
		</a>
	</h1>
	<div>{{ messages.intro }}</div>
	<h2>
		{{ messages.your_rules }}
		&nbsp;
		<button class="btn btn-primary btn-sm" :title="messages.add_a_rule" @click="showModal = true">
			<i class="fa fa-plus fa-2x" aria-hidden="true"></i>
		</button>
	</h2>
	<div v-if="rules.length === 0" v-cloak>
		{{ messages.no_rules }}
	</div>

	<ul class="list-group" v-cloak>
		<li class="list-group-item" v-for="rule in rules">
			<p class="pull-right">
				<button type="button" class="btn btn-xs btn-primary" v-on:click="toggleActive(rule)" :aria-label="messages.disable_this_rule" :title="messages.disable_this_rule" v-if="parseInt(rule.active)">
					<i class="fa fa-toggle-on" aria-hidden="true"></i> {{ messages.enabled }}
				</button>
				<button type="button" class="btn btn-xs btn-default" v-on:click="toggleActive(rule)" :aria-label="messages.enable_this_rule" :title="messages.enable_this_rule" v-else>
					<i class="fa fa-toggle-off" aria-hidden="true"></i> {{ messages.disabled }}
				</button>
				&nbsp;
				<button type="button" class="btn btn-xs btn-primary" v-on:click="editRule(rule)" :aria-label="messages.edit_this_rule" :title="messages.edit_this_rule">
					<i class="fa fa-pencil" aria-hidden="true"></i>
				</button>
				&nbsp;
				<button type="button" class="btn btn-xs btn-default" v-on:click="deleteRule(rule)" :aria-label="messages.delete_this_rule" :title="messages.delete_this_rule">
					<i class="fa fa-trash-o" aria-hidden="true"></i>
				</button>
			</p>
			<h3 class="list-group-item-heading">
				{{ messages.rule }} #{{ rule.id }}: {{ rule.name }}
			</h3>
			<pre class="list-group-item-text" v-if="rule.expression">{{ rule.expression }}</pre>
		</li>
	</ul>

	<div class="modal fade" ref="vuemodal" tabindex="-1" role="dialog" v-cloak>
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" :aria-label="messages.close" @click="showModal = false"  v-if="currentTheme === 'frio'"><span aria-hidden="true">&times;</span></button>
					<h3 v-if="rule.id">{{ messages.edit_the_rule }} "{{ rule.name }}"</h3>
					<h3 v-if="!rule.id">{{ messages.add_a_rule }}</h3>
				</div>
				<div class="modal-body">
					<form>
						<div class="alert alert-danger" role="alert" v-if="errorMessage">{{ errorMessage }}</div>
						<div class="form-group">
							<input class="form-control" :placeholder="messages.rule_name" v-model="rule.name">
						</div>
						<div class="form-group">
							<input class="form-control" :placeholder="messages.rule_expression" v-model="rule.expression">
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close" @click="resetForm()">{{ messages.cancel }}</button>
					<button slot="button" class="btn btn-primary" type="button" v-if="rule.id" v-on:click="saveRule(rule)">{{ messages.save_this_rule }}</button>
					<button slot="button" class="btn btn-primary" type="button" v-if="!rule.id" v-on:click="addRule()">{{ messages.add_a_rule }}</button>
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
	<pre v-cloak>
{{ itemJson }}
	</pre>
</div>
