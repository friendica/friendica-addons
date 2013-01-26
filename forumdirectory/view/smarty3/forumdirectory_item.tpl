
<div class="forumdirectory-item" id="forumdirectory-item-{{$id}}" >
	<div class="forumdirectory-photo-wrapper" id="forumdirectory-photo-wrapper-{{$id}}" > 
		<div class="forumdirectory-photo" id="forumdirectory-photo-{{$id}}" >
			<a href="{{$profile_link}}" class="forumdirectory-profile-link" id="forumdirectory-profile-link-{{$id}}" >
				<img class="forumdirectory-photo-img photo" src="{{$photo}}" alt="{{$alt_text}}" title="{{$alt_text}}" />
			</a>
		</div>
	</div>
	<div class="forumdirectory-profile-wrapper" id="forumdirectory-profile-wrapper-{{$id}}" >
		<div class="contact-name" id="forumdirectory-name-{{$id}}">{{$name}}</div>
		<div class="page-type">{{$page_type}}</div>
		{{if $pdesc}}<div class="forumdirectory-profile-title">{{$profile.pdesc}}</div>{{/if}}
    	<div class="forumdirectory-detailcolumns-wrapper" id="forumdirectory-detailcolumns-wrapper-{{$id}}">
        	<div class="forumdirectory-detailscolumn-wrapper" id="forumdirectory-detailscolumn1-wrapper-{{$id}}">	
			{{if $location}}
			    <dl class="location"><dt class="location-label">{{$location}}</dt>
				<dd class="adr">
					{{if $profile.address}}<div class="street-address">{{$profile.address}}</div>{{/if}}
					<span class="city-state-zip">
						<span class="locality">{{$profile.locality}}</span>{{if $profile.locality}}, {{/if}}
						<span class="region">{{$profile.region}}</span>
						<span class="postal-code">{{$profile.postal-code}}</span>
					</span>
					{{if $profile.country-name}}<span class="country-name">{{$profile.country-name}}</span>{{/if}}
				</dd>
				</dl>
			{{/if}}

			{{if $gender}}<dl class="mf"><dt class="gender-label">{{$gender}}</dt> <dd class="x-gender">{{$profile.gender}}</dd></dl>{{/if}}
			</div>	
			<div class="forumdirectory-detailscolumn-wrapper" id="forumdirectory-detailscolumn2-wrapper-{{$id}}">	
				{{if $marital}}<dl class="marital"><dt class="marital-label"><span class="heart">&hearts;</span>{{$marital}}</dt><dd class="marital-text">{{$profile.marital}}</dd></dl>{{/if}}

				{{if $homepage}}<dl class="homepage"><dt class="homepage-label">{{$homepage}}</dt><dd class="homepage-url"><a href="{{$profile.homepage}}" target="external-link">{{$profile.homepage}}</a></dd></dl>{{/if}}
			</div>
		</div>
	  	<div class="forumdirectory-copy-wrapper" id="forumdirectory-copy-wrapper-{{$id}}" >
			{{if $about}}<dl class="forumdirectory-copy"><dt class="forumdirectory-copy-label">{{$about}}</dt><dd class="forumdirectory-copy-data">{{$profile.about}}</dd></dl>{{/if}}
  		</div>
	</div>
</div>
