<div class="renderinfo" aria-hidden="true">{{$performance nofilter}}</div>
<div class="renderinfo">{{$initialization nofilter}}</div>
{{if $callstack}}
<details class="renderinfo-callstack">
	<summary>{{$callstack_title}}</summary>
	<pre>{{$callstack nofilter}}</pre>
</details>
{{/if}}
