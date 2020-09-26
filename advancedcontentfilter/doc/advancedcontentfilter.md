<style>
.advancedcontentfilter-content-wrapper {
	min-height: calc(100vh - 150px);
    padding: 15px;
    padding-bottom: 20px;
    margin-bottom: 20px;
    border: none;
    /*background-color: #fff;*/
    background-color: rgba(255,255,255,0.95);
    border-radius: 4px;
    position: relative;
    /*overflow: hidden;*/
    color: #555;
    box-shadow: 0 0 3px #dadada;
    -webkit-box-shadow: 0 0 3px #dadada;
    -moz-box-shadow: 0 0 3px #dadada;
}
</style>

<a href="advancedcontentfilter">🔙 Back to Advanced Content Filter Settings</a>

# Advanced Content Filter Help

The advanced Content Filter uses Symfony's Expression Language.
This help page includes a summary of [the Symfony's Expression Language documentation page.](https://symfony.com/doc/current/components/expression_language/syntax.html)

## Basics

The advanced content filter matches each post that is about to be displayed against each enabled rule you set.

A rule is a boolean expression that should return either `true` or `false` depending on post variables.

If the expression using a post variables returns `true`, the post will be collapsed and the matching rule name will be displayed above the collapsed content.

A post will be collapsed if at least one rule matches, but all matching rule names will be displayed above the collapsed content.

## Examples



## Expression Syntax

1. To block specific domains  `body matches "/\\.spiegel\\.de/"`
2. To block everything that contains the words `body matches "/Guten Morgen/"
3. To block every occurence of the word facebook with a space in front and after the word `body matches "//s facebook/s /"`
4. To colapse every post that contains more than 1 image `body matches "/(?:(?:(?:\\[url(?:=.*)?\\])?\\[img(?:=.*)?\\].*\\[\\/img\\]\\s*(?:\\[\\/url\\])?)\\s*){2}/"`



### Supported Literals

- **strings** - single and double quotes (e.g. `'hello'`).
- **numbers** - e.g. `103`.
- **arrays** - using JSON-like notation (e.g. `[1, 2]`).
- **hashes** - using JSON-like notation (e.g. `{ foo: 'bar' }`).
- **booleans** - `true` and `false`.
- **null** - `null`.

A backslash (``\``) must be escaped by 2 backslashes (``\\``) in a string and 4 backslashes (``\\\\``) in a regex::

`"a\\b" matches "/^a\\\\b$/"`

Control characters (e.g. ``\n``) in expressions are replaced with whitespace. To avoid this, escape the sequence with a single backslash (e.g.  ``\\n``).

### Supported Operators

The component comes with a lot of operators:

#### Arithmetic Operators

* ``+`` (addition)
* ``-`` (subtraction)
* ``*`` (multiplication)
* ``/`` (division)
* ``%`` (modulus)
* ``**`` (pow)

#### Bitwise Operators

* ``&`` (and)
* ``|`` (or)
* ``^`` (xor)

#### Comparison Operators

* ``==`` (equal)
* ``===`` (identical)
* ``!=`` (not equal)
* ``!==`` (not identical)
* ``<`` (less than)
* ``>`` (greater than)
* ``<=`` (less than or equal to)
* ``>=`` (greater than or equal to)
* ``matches`` (regex match)

To test if a string does *not* match a regex, use the logical ``not`` operator in combination with the ``matches`` operator:

`not ("foo" matches "/bar/")`

You must use parenthesis because the unary operator ``not`` has precedence over the binary operator ``matches``.

#### Logical Operators

* ``not`` or ``!``
* ``and`` or ``&&``
* ``or`` or ``||``

#### String Operators

* ``~`` (concatenation)

For example: ``firstName ~ " " ~ lastName``

#### Array Operators

* ``in`` (contain)
* ``not in`` (does not contain)

For example: ``user.group in ["human_resources", "marketing"]``

#### Numeric Operators

* ``..`` (range)

For example: ``user.age in 18..45``

#### Ternary Operators

* ``foo ? 'yes' : 'no'``
* ``foo ?: 'no'`` (equal to ``foo ? foo : 'no'``)
* ``foo ? 'yes'`` (equal to ``foo ? 'yes' : ''``)

### Supported variables

Here are a sample of the available variables you can use in your expressions.
You can also retrieve the variables of a specific post by pasting its URL below the rule list.

<table class="table-bordered table-condensed table-striped">
<thead>
	<tr>
		<th>Variable</th>
		<th>Type</th>
		<th>Sample Value</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th>author_id</th>
		<td>number</td>
		<td>6</td>
	</tr>
	<tr>
		<th>author_link</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/profile/hypolite</td>
	</tr>
	<tr>
		<th>author_name</th>
		<td>string</td>
		<td>Hypolite Petovan</td>
	</tr>
	<tr>
		<th>author_avatar</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/photo/41084997915a94a8c83cc39708500207-5.png</td>
	</tr>
	<tr>
		<th>owner_id</th>
		<td>number</td>
		<td>6</td>
	</tr>
	<tr>
		<th>owner_link</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/profile/hypolite</td>
	</tr>
	<tr>
		<th>owner_name</th>
		<td>string</td>
		<td>Hypolite Petovan</td>
	</tr>
	<tr>
		<th>owner_avatar</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/photo/41084997915a94a8c83cc39708500207-5.png</td>
	</tr>
	<tr>
		<th>contact_id</th>
		<td>number</td>
		<td>1</td>
	</tr>
	<tr>
		<th>uid</th>
		<td>number</td>
		<td>1</td>
	</tr>
	<tr>
		<th>id</th>
		<td>number</td>
		<td>791875</td>
	</tr>
	<tr>
		<th>parent</th>
		<td>number</td>
		<td>791875</td>
	</tr>
	<tr>
		<th>uri</th>
		<td>string</td>
		<td>urn:X-dfrn:friendica.mrpetovan.com:1:twit:978740198937907200</td>
	</tr>
	<tr>
		<th>thr_parent</th>
		<td>string</td>
		<td>urn:X-dfrn:friendica.mrpetovan.com:1:twit:978740198937907200</td>
	</tr>
	<tr>
		<th>parent_uri</th>
		<td>string</td>
		<td>urn:X-dfrn:friendica.mrpetovan.com:1:twit:978740198937907200</td>
	</tr>
	<tr>
		<th>content_warning</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>commented</th>
		<td>date</td>
		<td>2018-03-27 21:10:18</td>
	</tr>
	<tr>
		<th>created</th>
		<td>date</td>
		<td>2018-03-27 21:10:18</td>
	</tr>
	<tr>
		<th>edited</th>
		<td>date</td>
		<td>2018-03-27 21:10:18</td>
	</tr>
	<tr>
		<th>received</th>
		<td>date</td>
		<td>2018-03-27 21:10:18</td>
	</tr>
	<tr>
		<th>verb</th>
		<td>string</td>
		<td>http://activitystrea.ms/schema/1.0/post</td>
	</tr>
	<tr>
		<th>object_type</th>
		<td>string</td>
		<td>http://activitystrea.ms/schema/1.0/bookmark</td>
	</tr>
	<tr>
		<th>postopts</th>
		<td>string</td>
		<td>twitter&lang=pidgin;0.24032407407407:english;0.225:french;0.18055555555556</td>
	</tr>
	<tr>
		<th>plink</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/display/735a2029995abab33a5c006052376776</td>
	</tr>
	<tr>
		<th>guid</th>
		<td>string</td>
		<td>735a2029995abab33a5c006052376776</td>
	</tr>
	<tr>
		<th>wall</th>
		<td>boolean</td>
		<td>1</td>
	</tr>
	<tr>
		<th>private</th>
		<td>boolean</td>
		<td>0</td>
	</tr>
	<tr>
		<th>starred</th>
		<td>boolean</td>
		<td>0</td>
	</tr>
	<tr>
		<th>title</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>body</th>
		<td>string</td>
		<td>Over-compensation #[url=https://friendica.mrpetovan.com/search?tag=Street]Street[/url] #[url=https://friendica.mrpetovan.com/search?tag=Night]Night[/url] #[url=https://friendica.mrpetovan.com/search?tag=CarLights]CarLights[/url] #[url=https://friendica.mrpetovan.com/search?tag=Jeep]Jeep[/url] #[url=https://friendica.mrpetovan.com/search?tag=NoPeople]NoPeople[/url] #[url=https://friendica.mrpetovan.com/search?tag=Close]Close[/url]-up
	[attachment type='link' url='https://www.eyeem.com/p/120800309' title='Over-compensation Street Night Car Lights Jeep No | EyeEm' image='https://cdn.eyeem.com/thumb/b2f019738cbeef06e2f8c9517c6286a8adcd3a00-1522184820641/640/480']Photo by @[url=https://twitter.com/MrPetovan]MrPetovan[/url][/attachment]</td>
	</tr>
	<tr>
		<th>file</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>event_id</th>
		<td>number</td>
		<td>null
	<tr>
		<th>location</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>coord</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>app</th>
		<td>string</td>
		<td>EyeEm</td>
	</tr>
	<tr>
		<th>attach</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>rendered_hash</th>
		<td>string</td>
		<td>b70abdea8b362dc5dcf63e1b2836ad89</td>
	</tr>
	<tr>
		<th>rendered_html</th>
		<td>string</td>
		<td>
			Over-compensation #&lt;a href="https://friendica.mrpetovan.com/search?tag=Street" class="tag" title="Street"&gt;Street&lt;/a&gt; #&lt;a href="https://friendica.mrpetovan.com/search?tag=Night" class="tag" title="Night"&gt;Night&lt;/a&gt; #&lt;a href="https://friendica.mrpetovan.com/search?tag=CarLights" class="tag" title="CarLights"&gt;CarLights&lt;/a&gt; #&lt;a href="https://friendica.mrpetovan.com/search?tag=Jeep" class="tag" title="Jeep"&gt;Jeep&lt;/a&gt; #&lt;a href="https://friendica.mrpetovan.com/search?tag=NoPeople" class="tag" title="NoPeople"&gt;NoPeople&lt;/a&gt; #&lt;a href="https://friendica.mrpetovan.com/search?tag=Close" class="tag" title="Close"&gt;Close&lt;/a&gt;-up &lt;div class="type-link"&gt;&lt;a href="https://www.eyeem.com/p/120800309" target="_blank"&gt;&lt;img src="https://friendica.mrpetovan.com/proxy/bb/aHR0cHM6Ly9jZG4uZXllZW0uY29tL3RodW1iL2IyZjAxOTczOGNiZWVmMDZlMmY4Yzk1MTdjNjI4NmE4YWRjZDNhMDAtMTUyMjE4NDgyMDY0MS82NDAvNDgw" alt="" title="Over-compensation Street Night Car Lights Jeep No | EyeEm" class="attachment-image"&gt;&lt;/a&gt;&lt;br&gt;&lt;h4&gt;&lt;a href="https://www.eyeem.com/p/120800309"&gt;Over-compensation Street Night Car Lights Jeep No | EyeEm&lt;/a&gt;&lt;/h4&gt;&lt;blockquote&gt;Photo by @&lt;a href="https://twitter.com/MrPetovan" class="userinfo mention" title="MrPetovan"&gt;MrPetovan&lt;/a&gt;&lt;/blockquote&gt;&lt;sup&gt;&lt;a href="https://www.eyeem.com/p/120800309"&gt;www.eyeem.com&lt;/a&gt;&lt;/sup&gt;&lt;/div&gt;
		</td>
	</tr>
	<tr>
		<th>object</th>
		<td>string</td>
		<td>{"created_at":"Tue Mar 27 21:07:02 +0000 2018","id":978740198937907200,"id_str":"978740198937907200","full_text":"Over-compensation #Street #Night #CarLights #Jeep #NoPeople #Close-up https:\/\/t.co\/7w4ua13QA7","truncated":false,"display_text_range":[0,93],"entities":{"hashtags":[{"text":"Street","indices":[18,25]},{"text":"Night","indices":[26,32]},{"text":"CarLights","indices":[33,43]},{"text":"Jeep","indices":[44,49]},{"text":"NoPeople","indices":[50,59]},{"text":"Close","indices":[60,66]}],"symbols":[],"user_mentions":[],"urls":[{"url":"https:\/\/t.co\/7w4ua13QA7","expanded_url":"http:\/\/EyeEm.com\/p\/120800309","display_url":"EyeEm.com\/p\/120800309","indices":[70,93]}]},"source":"&lt;a href=\"http:\/\/www.eyeem.com\" rel=\"nofollow\"&gt;EyeEm&lt;\/a&gt;","in_reply_to_status_id":null,"in_reply_to_status_id_str":null,"in_reply_to_user_id":null,"in_reply_to_user_id_str":null,"in_reply_to_screen_name":null,"user":{"id":403748896,"id_str":"403748896","name":"\ud83d\udc30yp\ud83e\udd5ali\u271d\ufe0fe Pet\ud83e\udd5avan","screen_name":"MrPetovan","location":"NYC","description":"White male form of milquetoast. Avatar by @DearMsDear inspired by @TSG_LAB.\n\nFriendica\/Diaspora\/Mastodon: hypolite@friendica.mrpetovan.com","url":"https:\/\/t.co\/PcARi5OhQO","entities":{"url":{"urls":[{"url":"https:\/\/t.co\/PcARi5OhQO","expanded_url":"https:\/\/mrpetovan.com","display_url":"mrpetovan.com","indices":[0,23]}]},"description":{"urls":[]}},"protected":false,"followers_count":182,"friends_count":146,"listed_count":15,"created_at":"Wed Nov 02 23:13:14 +0000 2011","favourites_count":45826,"utc_offset":-14400,"time_zone":"Eastern Time (US &amp; Canada)","geo_enabled":false,"verified":false,"statuses_count":15554,"lang":"en","contributors_enabled":false,"is_translator":false,"is_translation_enabled":false,"profile_background_color":"000000","profile_background_image_url":"http:\/\/pbs.twimg.com\/profile_background_images\/370213187\/fond_twitter_mrpetovan.png","profile_background_image_url_https":"https:\/\/pbs.twimg.com\/profile_background_images\/370213187\/fond_twitter_mrpetovan.png","profile_background_tile":false,"profile_image_url":"http:\/\/pbs.twimg.com\/profile_images\/968008546322395136\/6qLCiu0o_normal.jpg","profile_image_url_https":"https:\/\/pbs.twimg.com\/profile_images\/968008546322395136\/6qLCiu0o_normal.jpg","profile_banner_url":"https:\/\/pbs.twimg.com\/profile_banners\/403748896\/1464321684","profile_link_color":"0084B4","profile_sidebar_border_color":"C0DEED","profile_sidebar_fill_color":"DDEEF6","profile_text_color":"000000","profile_use_background_image":true,"has_extended_profile":true,"default_profile":false,"default_profile_image":false,"following":false,"follow_request_sent":false,"notifications":false,"translator_type":"none"},"geo":null,"coordinates":null,"place":null,"contributors":null,"is_quote_status":false,"retweet_count":0,"favorite_count":0,"favorited":false,"retweeted":false,"possibly_sensitive":false,"lang":"en"}</td>
	</tr>
	<tr>
		<th>allow_cid</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>allow_gid</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>deny_cid</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>deny_gid</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>item_id</th>
		<td>number</td>
		<td>791875</td>
	</tr>
	<tr>
		<th>item_network</th>
		<td>string</td>
		<td>dfrn</td>
	</tr>
	<tr>
		<th>author_thumb</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/photo/0cb3d7231eb751139d7d309c7c686c49-5.png?ts=1522941604</td>
	</tr>
	<tr>
		<th>owner_thumb</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/photo/0cb3d7231eb751139d7d309c7c686c49-5.png?ts=1522941604</td>
	</tr>
	<tr>
		<th>network</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>url</th>
		<td>string</td>
		<td>https://friendica.mrpetovan.com/profile/hypolite</td>
	</tr>
	<tr>
		<th>name</th>
		<td>string</td>
		<td>Hypolite Petovan</td>
	</tr>
	<tr>
		<th>writable</th>
		<td>boolean</td>
		<td>0</td>
	</tr>
	<tr>
		<th>self</th>
		<td>boolean</td>
		<td>1</td>
	</tr>
	<tr>
		<th>cid</th>
		<td>number</td>
		<td>1</td>
	</tr>
	<tr>
		<th>alias</th>
		<td>string</td>
		<td></td>
	</tr>
	<tr>
		<th>event_created</th>
		<td>date</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_edited</th>
		<td>date</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_start</th>
		<td>date</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_finish</th>
		<td>date</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_summary</th>
		<td>string</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_desc</th>
		<td>string</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_location</th>
		<td>string</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_type</th>
		<td>string</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_nofinish</th>
		<td>string</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_adjust</th>
		<td>boolean</td>
		<td>null</td>
	</tr>
	<tr>
		<th>event_ignore</th>
		<td>boolean</td>
		<td>null</td>
	</tr>
	<tr>
		<th>pagedrop</th>
		<td>string</td>
		<td>true</td>
	</tr>
	<tr>
		<th>tags</th>
		<td>list</td>
		<td>
			<ol start="0">
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Street" target="_blank"&gt;street&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Night" target="_blank"&gt;night&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=CarLights" target="_blank"&gt;carlights&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Jeep" target="_blank"&gt;jeep&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=NoPeople" target="_blank"&gt;nopeople&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Close" target="_blank"&gt;close&lt;/a&gt;</li>
				<li>@&lt;a href="https://twitter.com/MrPetovan" target="_blank"&gt;mrpetovan&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Close-up" target="_blank"&gt;close-up&lt;/a&gt;</li>
			</ol>
		</td>
	</tr>
	<tr>
		<th>hashtags</th>
		<td>list</td>
		<td>
			<ol start="0">
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Street" target="_blank"&gt;street&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Night" target="_blank"&gt;night&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=CarLights" target="_blank"&gt;carlights&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Jeep" target="_blank"&gt;jeep&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=NoPeople" target="_blank"&gt;nopeople&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Close" target="_blank"&gt;close&lt;/a&gt;</li>
				<li>#&lt;a href="https://friendica.mrpetovan.com/search?tag=Close-up" target="_blank"&gt;close-up&lt;/a&gt;</li>
			</ol>
		</td>
	</tr>
	<tr>
		<th>mentions</th>
		<td>string</td>
		<td>
			<ol start="0">
				<li>@&lt;a href="https://twitter.com/MrPetovan" target="_blank"&gt;mrpetovan&lt;/a&gt;</li>
			</ol>
		</td>
	</tr>
</tbody>
</table>
