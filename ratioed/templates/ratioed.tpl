<script type="text/javascript" src="view/theme/frio/js/mod_admin.js?v={{constant('\Friendica\App::VERSION')}}"></script>
<link rel="stylesheet" href="view/theme/frio/css/mod_admin.css?v={{constant('\Friendica\App::VERSION')}}" type="text/css" media="screen"/>

<div id="admin-users" class="adminpage generic-page-wrapper">
	<h1>
		{{$title}} - {{$page}} ({{$count}})
		<a href="{{$base_url}}/ratioed/help"><i style="float: right; font-size: 60%" class="fa fa-question-circle fa-fw" aria-hidden="true"></i></a>
	</h1>
	<form action="{{$baseurl}}/{{$query_string}}" method="post">
		<input type="hidden" name="form_security_token" value="{{$form_security_token}}">
		<table id="users" class="table table-hover">
			<thead>
				<tr>
					<th></th>
					<th></th>
					{{foreach $th_users as $k=>$th}}
						{{if $k < 2 || $order_users == $th.1 || ($k==4 && !in_array($order_users,[$th_users.2.1, $th_users.3.1, $th_users.5.1])) }}
						<th class="th-{{$k}}">
							<a href="{{$baseurl}}/ratioed?o={{if $order_direction_users == "+"}}-{{/if}}{{$th.1}}" class="table-order">
								{{if $order_users == $th.1}}
									{{if $order_direction_users == "+"}}
									&#8595;
									{{else}}
									&#8593;
									{{/if}}
								{{else}}
								&#8597;
								{{/if}}
								{{$th.0}}
							</a>
						</th>
						{{/if}}
					{{/foreach}}
					<th></th>
				</tr>
			</thead>
			<tbody>
			{{foreach $users as $u}}
				<tr id="user-{{$u.uid}}" class="{{if $u.ratioed || $u.reply_guy}}blocked{{/if}}">
					<td></td>
					<td><img class="avatar-nano" src="{{$u.micro}}" title="{{$u.nickname}}"></td>
					<td><a href="{{$u.url}}" title="{{$u.nickname}}"> {{$u.name}}</a></td>
					<td>{{$u.email}}</td>
				{{if $order_users == $th_users.2.1}}
					<td>{{$u.register_date}}</td>
				{{/if}}

				{{if $order_users == $th_users.3.1}}
					<td>{{$u.login_date}}</td>
				{{/if}}

				{{if $order_users == $th_users.5.1}}
					<td>
						<i class="fa
							{{if $u.page_flags_raw==0}}fa-user{{/if}}		{{* PAGE_NORMAL *}}
							{{if $u.page_flags_raw==1}}fa-bullhorn{{/if}}		{{* PAGE_SOAPBOX *}}
							{{if $u.page_flags_raw==2}}fa-users{{/if}}		{{* PAGE_COMMUNITY *}}
							{{if $u.page_flags_raw==3}}fa-heart{{/if}}		{{* PAGE_FREELOVE *}}
							{{if $u.page_flags_raw==4}}fa-rss{{/if}}		{{* PAGE_BLOG *}}
							{{if $u.page_flags_raw==5}}fa-user-secret{{/if}}	{{* PAGE_PRVGROUP *}}
							" title="{{$u.page_flags}}">
						</i>
						{{if $u.page_flags_raw==0 && $u.account_type_raw > 0}}
						<i class="fa
							{{if $u.account_type_raw==1}}fa-sitemap{{/if}}		{{* ACCOUNT_TYPE_ORGANISATION *}}
							{{if $u.account_type_raw==2}}fa-newspaper-o{{/if}}	{{* ACCOUNT_TYPE_NEWS *}}
							{{if $u.account_type_raw==3}}fa-comments{{/if}}		{{* ACCOUNT_TYPE_COMMUNITY *}}
							" title="{{$u.account_type}}">
						</i>
						{{/if}}
						{{if $u.is_admin}}<i class="fa fa-user-secret text-primary" title="{{$siteadmin}}"></i>{{/if}}
						{{if $u.account_expired}}<i class="fa fa-clock-o text-warning" title="{{$accountexpired}}"></i>{{/if}}
					</td>
				{{/if}}

				{{if !in_array($order_users,[$th_users.2.1, $th_users.3.1, $th_users.5.1]) }}
					<td>{{$u.lastitem_date}}</td>
				{{/if}}

					<td class="text-right">
						<button type="button" class="btn-link admin-settings-action-link" onclick="return details({{$u.uid}})"><span class="caret"></span></button>
					</td>
				</tr>
				<tr id="user-{{$u.uid}}-detail" class=" details hidden {{if $u.blocked != 0}}blocked{{/if}}">
					<td>&nbsp;</td>
					<td colspan="4">
					{{if $order_users != $th_users.2.1}}
						<p>
							<a href="{{$baseurl}}/ratioed?o={{if $order_direction_users == "+"}}-{{/if}}{{$th_users.2.1}}" class="btn-link table-order">
							&#8597; {{$th_users.2.0}}</a> : {{$u.register_date}}
						</p>
					{{/if}}

					{{if $order_users != $th_users.3.1}}
						<p>
							<a href="{{$baseurl}}/ratioed?o={{if $order_direction_users == "+"}}-{{/if}}{{$th_users.3.1}}" class="btn-link table-order">
								&#8597; {{$th_users.3.0}}</a> : {{$u.login_date}}
						</p>
					{{/if}}

					{{if $order_users != $th_users.4.1}}
						<p>
							<a href="{{$baseurl}}/ratioed?o={{if $order_direction_users == "+"}}-{{/if}}{{$th_users.4.1}}" class="btn-link table-order">
								&#8597; {{$th_users.4.0}}</a> : {{$u.lastitem_date}}
						</p>
					{{/if}}

					{{if in_array($order_users,[$th_users.2.1, $th_users.3.1, $th_users.4.1]) }}
						<p>
							<a href="{{$baseurl}}/ratioed?o={{if $order_direction_users == "+"}}-{{/if}}{{$th_users.5.1}}" class="btn-link table-order">
								&#8597; {{$th_users.5.0}}</a> : {{$u.page_flags}}{{if $u.page_flags_raw==0 && $u.account_type_raw > 0}}, {{$u.account_type}}{{/if}} {{if $u.is_admin}}({{$siteadmin}}){{/if}} {{if $u.account_expired}}({{$accountexpired}}){{/if}}
						</p>
					{{/if}}

					{{foreach $th_users as $k=>$th}}
					{{if $order_users != $th.1 && $k > 5}}
						<p>
							{{$th.0}} : {{$u[$th.2]}}
						</p>
					{{/if}}
					{{/foreach}}

					</td>
					<td class="text-right"></td>
				</tr>
			{{/foreach}}
			</tbody>
		</table>
		{{$pager nofilter}}
	</form>
</div>
